<?php

// Function to load configuration from file
function loadConfig($configFile) {
    return require $configFile;
}

// Function to check if a table exists in the database
function tableExists($pdo, $tableName) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$tableName]);
    return $stmt->rowCount() > 0;
}

// Function to create a table based on XML structure
function createTableFromXML($pdo, $tableName, $xml) {
    $columns = [];
    $firstElement = $xml->children()[0];
    foreach ($firstElement->children() as $child) {
        $columnName = $child->getName();
        $columns[$columnName] = 'VARCHAR(255)'; // Assuming all columns are VARCHAR(255), you can adjust as needed
    }
    // Check if table already exists
    if (!tableExists($pdo, $tableName)) {
        // Generate SQL statement to create table
        $sql = "CREATE TABLE $tableName (";
        foreach ($columns as $columnName => $columnType) {
            $sql .= "$columnName $columnType, ";
        }
        $sql = rtrim($sql, ', '); // Remove the last comma and space
        $sql .= ")";

        // Execute SQL statement to create table
        $pdo->exec($sql);
    }
}

// Function to process XML data and push it to the database
function processXML($xmlFile, $pdo, $logFile) {
    try {
        // Check if XML file exists
        if (!file_exists($xmlFile)) {
            throw new Exception("XML file '$xmlFile' does not exist.");
        }

        // Parse XML file
        $xml = simplexml_load_file($xmlFile);
        $tableName = $xml->getName(); 

        // Create table if not exists
        createTableFromXML($pdo, $tableName, $xml);

        // Prepare INSERT statement
        $columns = [];
        $placeholders = [];
        foreach ($xml->children()[0]->children() as $child) {
            $columns[] = $child->getName();
            $placeholders[] = '?';
        }
        $columnList = implode(', ', $columns);
        $placeholderList = implode(', ', $placeholders);
        $sql = "INSERT INTO $tableName ($columnList) VALUES ($placeholderList)";
        $stmt = $pdo->prepare($sql);

        // Insert data into the table
        foreach ($xml->children() as $item) {
            $values = [];
            foreach ($item->children() as $child) {
                $values[] = (string) $child;
            }
            // Check if the data already exists in the database (Assume first column is unique)
            $checkIfExists = "SELECT COUNT(*) FROM $tableName WHERE $columns[0] = ?";
            $checkStmt = $pdo->prepare($checkIfExists);
            $checkStmt->execute([$values[0]]); 
            $exists = $checkStmt->fetchColumn();

            if (!$exists) {
                $stmt->execute($values);
            }
        }

        // Close database connection
        $pdo = null;
    } catch (Exception $e) {
        // Log error
        logError($e->getMessage(), $logFile);
    }
}

// Function to process CSV data and push it to the database
function processCSV($csvFile, $delimiter, $pdo, $logFile) {
    try {
        // Check if CSV file exists
        if (!file_exists($csvFile)) {
            throw new Exception("CSV file '$csvFile' does not exist.");
        }
        // IF using CSV- Insert logic to process CSV data and push it to the database
    } catch (Exception $e) {
        // Log error
        logError($e->getMessage(), $logFile);
    }
}

// Function to log errors to a file
function logError($message, $logFile) {
    // Log error message to file
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $message" . PHP_EOL, FILE_APPEND);
}

function main() {
    // Check if command-line arguments are provided
    if (isset($argv)) {        
        global $argv;
        if (count($argv) < 2) {
        echo "Usage: php datafeed.php config.php\n";
        exit(1);

        // Extract the configuration file from command-line arguments
         $configFile = $argv[1];
        }
    } else {

        $configFile = 'config.php';
    } 
   
    // Load configuration
    $config = loadConfig($configFile);

    // Get the selected database configuration
    $selectedDatabase = $config['selected_database'];
    $logFile = $config['logFile'];

    // Check if the selected database configuration exists
    try {
        if (!isset($config['databases'][$selectedDatabase])) {
            throw new Exception("Selected database configuration not found.");
        }
    } catch (Exception $e) {
        // Log error
        logError($e->getMessage(), $logFile);
        exit("An error occurred. Please check the log file for details.");
    }

    // Get PDO type and database configuration
    $pdoType = $config['databases'][$selectedDatabase]['pdo_type'];
    $dbConfig = $config['databases'][$selectedDatabase];

    // Database connection details
    try {
        if ($pdoType === 'sqlite') {
            // SQLite connection
            $pdo = new PDO("$pdoType:" . $dbConfig['dbname']);
        } else {
            // MySQL connection
            $pdo = new PDO("$pdoType:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error reporting

        // Determine data source type
        $dataSourceType = $config['data_source']['type'];
        $dataSourcePath = $config['data_source']['path'];

        // Process data based on the data source type
        switch ($dataSourceType) {
            case 'xml':
                processXML($dataSourcePath, $pdo, $config['logFile']);
                break;
            case 'csv':
                processCSV($dataSourcePath, $config['data_source']['delimiter'], $pdo, $config['logFile']);
                break;
            default:
                echo "Unsupported data source type.\n";
                break;
        }

    } catch (PDOException $e) {
        //Log error
        logError($e->getMessage(), $logFile);
         //echo "Error connecting to the database: " . $e->getMessage() . "\n";
    } finally {
        // Close database connection
        if (isset($pdo)) {
            $pdo = null;
        }
    }
}

// Execute main function
main();

?>
