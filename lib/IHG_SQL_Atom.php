<?php

class IHG_SQL_Atom implements IHG_SQL_Atom_Interface {
	
	const NULL = null;
	
	const NOT_NULL = 0x00;
	
	private $sql_atom;
	
	private $bound_values;
	
	private $_tmp_table_name;
	
	public function __construct($sql_atom, array $bound_values = null) {
		$this->sql_atom = $sql_atom;
		$this->bound_values = $bound_values ? $bound_values : array();
	}
	
	public function sql_atom() {
		return $this->sql_atom;
	}
	
	public function bind_value($value) {
		$this->bound_values[] = $value;
		
		return $this;
	}
	
	public function bind_values(array $values) {
		$this->bound_values += $values;
		
		return $this;
	}
	
	public function bound_values() {
		return $this->bound_values;
	}

	public function prepend_table_name($table_name) {
		$this->_tmp_table_name = $table_name;
		
		$this->sql_atom = preg_replace_callback(
			'{([a-zA-Z\.\_]+(?!\s*\())((?:[,\s\)]|$))}',
			array($this, '_prepend_table_name_callback'),
			$this->sql_atom);
		
		$this->_tmp_table_name = null;
		
		return $this;
	}
	
	private function _prepend_table_name_callback($matches) {
		static $sql_constants;
		
		if(!$sql_constants) {
			$sql_constants = explode("\n", 'ADD
				ALL
				ALTER
				ANALYZE
				AND
				AS
				ASC
				ASENSITIVE
				BEFORE
				BETWEEN
				BIGINT
				BINARY
				BLOB
				BOTH
				BY
				CALL
				CASCADE
				CASE
				CHANGE
				CHAR
				CHARACTER
				CHECK
				COLLATE
				COLUMN
				CONDITION
				CONSTRAINT
				CONTINUE
				CONVERT
				CREATE
				CROSS
				CURRENT_DATE
				CURRENT_TIME
				CURRENT_TIMESTAMP
				CURRENT_USER
				CURSOR
				DATABASE
				DATABASES
				DAY_HOUR
				DAY_MICROSECOND
				DAY_MINUTE
				DAY_SECOND
				DEC
				DECIMAL
				DECLARE
				DEFAULT
				DELAYED
				DELETE
				DESC
				DESCRIBE
				DETERMINISTIC
				DISTINCT
				DISTINCTROW
				DIV
				DOUBLE
				DROP
				DUAL
				EACH
				ELSE
				ELSEIF
				ENCLOSED
				ESCAPED
				EXISTS
				EXIT
				EXPLAIN
				FALSE
				FETCH
				FLOAT
				FLOAT4
				FLOAT8
				FOR
				FORCE
				FOREIGN
				FROM
				FULLTEXT
				GRANT
				GROUP
				HAVING
				HIGH_PRIORITY
				HOUR_MICROSECOND
				HOUR_MINUTE
				HOUR_SECOND
				IF
				IGNORE
				IN
				INDEX
				INFILE
				INNER
				INOUT
				INSENSITIVE
				INSERT
				INT
				INT1
				INT2
				INT3
				INT4
				INT8
				INTEGER
				INTERVAL
				INTO
				IS
				ITERATE
				JOIN
				KEY
				KEYS
				KILL
				LEADING
				LEAVE
				LEFT
				LIKE
				LIMIT
				LINES
				LOAD
				LOCALTIME
				LOCALTIMESTAMP
				LOCK
				LONG
				LONGBLOB
				LONGTEXT
				LOOP
				LOW_PRIORITY
				MATCH
				MEDIUMBLOB
				MEDIUMINT
				MEDIUMTEXT
				MIDDLEINT
				MINUTE_MICROSECOND
				MINUTE_SECOND
				MOD
				MODIFIES
				NATURAL
				NOT
				NO_WRITE_TO_BINLOG
				NULL
				NUMERIC
				ON
				OPTIMIZE
				OPTION
				OPTIONALLY
				OR
				ORDER
				OUT
				OUTER
				OUTFILE
				PRECISION
				PRIMARY
				PROCEDURE
				PURGE
				READ
				READS
				REAL
				REFERENCES
				REGEXP
				RELEASE
				RENAME
				REPEAT
				REPLACE
				REQUIRE
				RESTRICT
				RETURN
				REVOKE
				RIGHT
				RLIKE
				SCHEMA
				SCHEMAS
				SECOND_MICROSECOND
				SELECT
				SENSITIVE
				SEPARATOR
				SET
				SHOW
				SMALLINT
				SONAME
				SPATIAL
				SPECIFIC
				SQL
				SQLEXCEPTION
				SQLSTATE
				SQLWARNING
				SQL_BIG_RESULT
				SQL_CALC_FOUND_ROWS
				SQL_SMALL_RESULT
				SSL
				STARTING
				STRAIGHT_JOIN
				TABLE
				TERMINATED
				THEN
				TINYBLOB
				TINYINT
				TINYTEXT
				TO
				TRAILING
				TRIGGER
				TRUE
				UNDO
				UNION
				UNIQUE
				UNLOCK
				UNSIGNED
				UPDATE
				USAGE
				USE
				USING
				UTC_DATE
				UTC_TIME
				UTC_TIMESTAMP
				VALUES
				VARBINARY
				VARCHAR
				VARCHARACTER
				VARYING
				WHEN
				WHERE
				WHILE
				WITH
				WRITE
				XOR
				YEAR_MONTH
				ZEROFILL
				ASENSITIVE
				CALL
				CONDITION
				CONNECTION
				CONTINUE
				CURSOR
				DECLARE
				DETERMINISTIC
				EACH
				ELSEIF
				EXIT
				FETCH
				GOTO
				INOUT
				INSENSITIVE
				ITERATE
				LABEL
				LEAVE
				LOOP
				MODIFIES
				OUT
				READS
				RELEASE
				REPEAT
				RETURN
				SCHEMA
				SCHEMAS
				SENSITIVE
				SPECIFIC
				SQL
				SQLEXCEPTION
				SQLSTATE
				SQLWARNING
				TRIGGER
				UNDO
				UPGRADE
				WHILE');
		}
				
		if(strstr($matches[1], '.') || in_array($matches[1], $sql_constants)) {
			return $matches[0];
		} else {
			return sprintf('%s.%s%s',
				$this->_tmp_table_name,
				$matches[1],
				$matches[2]);
		}
	}
}