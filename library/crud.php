<?php
/*
 * Credits - Github 
 * @Author Rory Standley <rorystandley@gmail.com>
 * @Version 1.0
 * @Package Database
 */
define('DOMAIN_URL', 'https://admin.uquiz.xyz/');
define('JWT_SECRET_KEY', 'Check@boys123');

class Database
{
    /*
     * Create variables for credentials to MySQL database
     * The variables have been declared as private. This
     * means that they will only be available with the 
     * Database class
     */
    private $db_host;
    private $db_user;
    private $db_pass;
    private $db_name;

    public function __construct()
    {
        // Detect if running on localhost or production
        // Improved localhost detection for CLI and web server
        if (php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg') {
            $is_local = true;
        } else {
            $is_local = (
                isset($_SERVER['HTTP_HOST']) &&
                (
                    $_SERVER['HTTP_HOST'] === 'localhost' ||
                    $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
                    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
                    strpos($_SERVER['HTTP_HOST'], '127.0.0.1:') === 0
                )
            );
        }

        if ($is_local) {
            $this->db_host = 'localhost';
            $this->db_user = 'root';
            $this->db_pass = '';
            $this->db_name = 'admin_quiz';
        } else {
            $this->db_host = 'localhost';
            $this->db_user = 'forcopix_uquizadmin';
            $this->db_pass = 'Jgqk_NROs25_98';
            $this->db_name = 'forcopix_uquizadmin';
        }
    }



    /*
     * Extra variables that are required by other function such as boolean con variable
     */
    private $con = false; // Check to see if the connection is active
    private $myconn = ""; // This will be our mysqli object
    private $result = array(); // Any results from a query will be stored here
    private $myQuery = ""; // used for debugging process with SQL return
    private $numResults = ""; // used for returning the number of rows

    // Function to make connection to database

    public function connect()
    {
        if (!$this->con) {
            $this->myconn = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);  // mysql_connect() with variables defined at the start of Database class
            if ($this->myconn->connect_errno > 0) {
                array_push($this->result, $this->myconn->connect_error);
                return false; // Problem selecting database return FALSE
            } else {
                $this->con = true;
                return true; // Connection has been made return TRUE
            }
        } else {
            return true; // Connection has already been made return TRUE 
        }
    }

    // Function to disconnect from the database
    public function disconnect()
    {
        // If there is a connection to the database
        if ($this->con) {
            // We have found a connection, try to close it
            if ($this->myconn->close()) {
                // We have successfully closed the connection, set the connection variable to false
                $this->con = false;
                // Return true tjat we have closed the connection
                return true;
            } else {
                // We could not close the connection, return false
                return false;
            }
        }
    }

    public function sql($sql)
    {
        $query = $this->myconn->query($sql);
        $this->myQuery = $sql; // Pass back the SQL
        if ($query) {
            // If the query returns >= 1 assign the number of rows to numResults
            $this->numResults = 0;
            if (isset($query->num_rows) && ($query->num_rows > 0)) {
                $this->numResults = $query->num_rows;
            }
            // Loop through the query results by the number of rows returned
            for ($i = 0; $i < $this->numResults; $i++) {
                $r = $query->fetch_array();
                $key = array_keys($r);
                for ($x = 0; $x < count($key); $x++) {
                    // Sanitizes keys so only alphavalues are allowed
                    if (!is_int($key[$x])) {
                        if ($query->num_rows >= 1) {
                            $this->result[$i][$key[$x]] = $r[$key[$x]];
                        } else {
                            $this->result = null;
                        }
                    }
                }
            }
            return true; // Query was successful
        } else {
            array_push($this->result, $this->myconn->error);
            return false; // No rows where returned
        }
    }

    // Function to SELECT from the database
    public function select($table, $rows = '*', $join = null, $where = null, $order = null, $limit = null)
    {
        // Create query from the variables passed to the function
        $q = 'SELECT ' . $rows . ' FROM ' . $table;
        if ($join != null) {
            $q .= ' JOIN ' . $join;
        }
        if ($where != null) {
            $q .= ' WHERE ' . $where;
        }
        if ($order != null) {
            $q .= ' ORDER BY ' . $order;
        }
        if ($limit != null) {
            $q .= ' LIMIT ' . $limit;
        }
        // echo $table;
        $this->myQuery = $q; // Pass back the SQL
        // Check to see if the table exists
        if ($this->tableExists($table)) {
            // The table exists, run the query
            $query = $this->myconn->query($q);
            if ($query) {
                // If the query returns >= 1 assign the number of rows to numResults
                $this->numResults = $query->num_rows;
                // Loop through the query results by the number of rows returned
                for ($i = 0; $i < $this->numResults; $i++) {
                    $r = $query->fetch_array();
                    $key = array_keys($r);
                    for ($x = 0; $x < count($key); $x++) {
                        // Sanitizes keys so only alphavalues are allowed
                        if (!is_int($key[$x])) {
                            if ($query->num_rows >= 1) {
                                $this->result[$i][$key[$x]] = $r[$key[$x]];
                            } else {
                                $this->result[$i][$key[$x]] = null;
                            }
                        }
                    }
                }
                return true; // Query was successful
            } else {
                array_push($this->result, $this->myconn->error);
                return false; // No rows where returned
            }
        } else {
            return false; // Table does not exist
        }
    }

    // Function to insert into the database
    public function insert($table, $params = array())
    {
        // Check to see if the table exists
        if ($this->tableExists($table)) {
            $sql = 'INSERT INTO `' . $table . '` (`' . implode('`, `', array_keys($params)) . '`) VALUES ("' . implode('", "', $params) . '")';
            $this->myQuery = $sql; // Pass back the SQL
            // echo $sql;
            // Make the query to insert to the database
            if ($ins = $this->myconn->query($sql)) {
                array_push($this->result, $this->myconn->insert_id);
                return true; // The data has been inserted
            } else {
                array_push($this->result, $this->myconn->error);
                return false; // The data has not been inserted
            }
        } else {
            return false; // Table does not exist
        }
    }

    //Function to delete table or row(s) from database
    public function delete($table, $where = null)
    {
        // Check to see if table exists
        if ($this->tableExists($table)) {
            // The table exists check to see if we are deleting rows or table
            if ($where == null) {
                $delete = 'DROP TABLE ' . $table; // Create query to delete table
            } else {
                $delete = 'DELETE FROM ' . $table . ' WHERE ' . $where; // Create query to delete rows
            }
            // Submit query to database
            if ($del = $this->myconn->query($delete)) {
                array_push($this->result, $this->myconn->affected_rows);
                $this->myQuery = $delete; // Pass back the SQL
                return true; // The query exectued correctly
            } else {
                array_push($this->result, $this->myconn->error);
                return false; // The query did not execute correctly
            }
        } else {
            return false; // The table does not exist
        }
    }

    // Function to update row in database
    public function update($table, $params = array(), $where)
    {
        // Check to see if table exists
        if ($this->tableExists($table)) {
            // Create Array to hold all the columns to update
            $args = array();
            foreach ($params as $field => $value) {
                // Seperate each column out with it's corresponding value
                $args[] = $field . '="' . $value . '"';
            }
            // Create the query
            $sql = 'UPDATE ' . $table . ' SET ' . implode(',', $args) . ' WHERE ' . $where;
            // Make query to database
            $this->myQuery = $sql; // Pass back the SQL
            if ($query = $this->myconn->query($sql)) {
                array_push($this->result, $this->myconn->affected_rows);
                return true; // Update has been successful
            } else {
                array_push($this->result, $this->myconn->error);
                return false; // Update has not been successful
            }
        } else {
            return false; // The table does not exist
        }
    }

    // Private function to check if table exists for use with queries
    private function tableExists($table)
    {
        $tablesInDb = $this->myconn->query('SHOW TABLES FROM `' . $this->db_name . '` LIKE "' . $table . '"');
        if ($tablesInDb) {
            if ($tablesInDb->num_rows == 1) {
                return true; // The table exists
            } else {
                array_push($this->result, $table . " does not exist in this database");
                return false; // The table does not exist
            }
        }
    }

    // Public function to return the data to the user
    public function getResult()
    {
        $val = $this->result;
        $this->result = array();
        return $val;
    }

    //Pass the SQL back for debugging
    public function getSql()
    {
        $val = $this->myQuery;
        $this->myQuery = array();
        return $val;
    }

    //Pass the number of rows back
    public function numRows()
    {
        $val = $this->numResults;
        $this->numResults = array();
        return $val;
    }

    // Escape your string
    public function escapeString($data)
    {
        return $this->myconn->real_escape_string($data);
    }

    //get last inser id
    public function insert_id()
    {
        return $this->myconn->insert_id;
    }
}
