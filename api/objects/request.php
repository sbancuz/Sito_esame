<?php
class Request
{

    // database connection and table name
    private $conn;
    private $table_name;

    // object properties
    public $fields = [];

    // constructor with $db as database connection
    public function __construct($db, $table_name = NULL)
    {
        $this->conn = $db;
        if ($table_name != NULL)
            $this->table_name = $table_name;
        $query = 'SELECT COLUMN_NAME
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA="ztihifnn_wp549" AND TABLE_NAME="' . $this->table_name . '"';
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($this->fields, $row['COLUMN_NAME']);
        }
    }

    // read 
    function read($input = NULL)
    {

        // select all query
        if ($this->table_name == "countries") {
            if ($input) {
                $query = "SELECT * FROM " . $this->table_name . " WHERE year=" . $input . ";";
            } else {
                $query = "SELECT * FROM " . $this->table_name . ";";
            }
        } else {
            if ($input) {
                $query = "SELECT";
                foreach ($this->fields as $field) {
                    $query = $query . " t." . $field . ",";
                }
                $query = $query . "c.ISO_char
                    FROM " . $this->table_name . " AS t, countries AS c 
                    WHERE year=" . $input . " AND c.Country = t.Country;";
            } else {
                $query = "SELECT";
                foreach ($this->fields as $field) {
                    $query = $query . " t." . $field . ",";
                }
                $query = $query . "c.ISO_char
                    FROM " . $this->table_name . " AS t, countries AS c 
                    WHERE c.Country = t.Country;";
            }
            array_push($this->fields, "ISO_char");
        }
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }

    // create 
    function create($data)
    {
        // query to insert record
        $query = "INSERT INTO " . $this->table_name . " SET";
        foreach ($this->fields as $field) {
            if ($field == 'ID') {
                continue;
            }
            $query = $query . " " . $field . "=:" . $field . ",";
        }
        $query = substr($query, 0, -1);

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        foreach ($this->fields as $field) {
            if ($field == 'ID') {
                continue;
            }
            $data[$field] = htmlspecialchars(strip_tags($data[$field]));
        }

        // bind values
        foreach ($this->fields as $field) {
            if ($field == 'ID') {
                continue;
            }
            $stmt->bindParam(":" . $field, $data[$field]);
        }

        // execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // delete the product
    function delete($id)
    {

        // delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE ID = ?";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->id = htmlspecialchars(strip_tags($id));

        // bind id of record to delete
        $stmt->bindParam(1, $this->id);

        // execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
    // update the product
    function update($data)
    {

        // update query
        $query = "UPDATE " . $this->table_name . " SET";
        foreach (array_keys($data) as $field) {
            if ($field == 'ID' || $field == 'Country') {
                continue;
            }
            $query = $query . " " . $field . "=:" . $field . ",";
        }
        $query = substr($query, 0, -1);
        $query = $query . " WHERE ID = :ID";
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // sanitize
        foreach (array_keys($data) as $field) {
            if ($field == 'Country') {
                continue;
            }
            $field = htmlspecialchars(strip_tags($field));
        }

        // bind values
        foreach (array_keys($data) as $field) {
            if ($field == 'Country') {
                continue;
            }
            $stmt->bindParam(":" . $field, $data[$field]);
        }

        // execute the query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // schema 
    function schema()
    {

        // select all query
        $query = "SHOW TABLES;";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }

    // year
    function year()
    {
        // select all query
        $query = "SELECT DISTINCT Year FROM ".$this->table_name." ORDER By Year ASC";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }

    // consumption
    function consumption()
    {
        // select all query
        $query = "SELECT g.Year, co.Country, c.Consumption_mtoe as Coal_mtoe, g.Consumption_mtoe as Gas_mtoe,
         n.Consumption_mtoe as Nuclear_mtoe, o.Consumption_mtoe as Oil_mtoe, 
         r.Solar_cons_mtoe as Solar_mtoe, r.Wind_cons_mtoe as Wind_mtoe, r.Geo_cons_mtoe as Geo_mtoe, r.Hydro_cons_mtoe as Hydro_mtoe 
         FROM coal as c, gas as g, nuclear as n, oil as o, renewables as r, countries as co 
         WHERE (c.Country in (co.Country) AND g.Country in (co.Country) AND n.Country in (co.Country) AND o.Country in (co.Country) AND r.Country in (co.Country) )
         AND( g.Year = c.Year AND r.Year = o.Year AND n.Year = c.Year AND n.Year = r.Year AND g.Year = o.Year) ";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }

    // production
    function production()
    {
        // select all query
        $query = "SELECT g.Year, co.Country, c.Production_mtoe as Coal_mtoe, g.Production_mtoe as Gas_mtoe,
          n.Production_mtoe as Nuclear_mtoe, o.Production_mtoe as Oil_mtoe, r.Solar_gen_mtoe as Solar_mtoe,
          r.Wind_gen_mtoe as Wind_mtoe, r.Geo_prod_mtoe as Geo_mtoe, r.Hydro_prod_mtoe as Hydro_mtoe FROM coal as c, gas as g, nuclear as n, oil as o,
          renewables as r, countries as co WHERE 
          (c.Country in (co.Country) AND g.Country in (co.Country) AND n.Country in (co.Country) AND o.Country in (co.Country)
          AND r.Country in (co.Country) ) AND( g.Year = c.Year AND r.Year = o.Year AND n.Year = c.Year AND n.Year = r.Year AND g.Year = o.Year)";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }
}
