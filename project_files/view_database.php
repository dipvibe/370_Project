<?php
include "connection.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .table-container { margin-bottom: 40px; overflow-x: auto; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #6c16be; }
        .table thead { background-color: #333; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Database Viewer</h1>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>

        <?php
        // Get all tables
        $tables_result = mysqli_query($conn, "SHOW TABLES");
        
        if (!$tables_result) {
            echo "<div class='alert alert-danger'>Error fetching tables: " . mysqli_error($conn) . "</div>";
        } else {
            while ($table_row = mysqli_fetch_array($tables_result)) {
                $table_name = $table_row[0];
                
                echo "<div class='table-container'>";
                echo "<h2>Table: $table_name</h2>";
                
                $data_result = mysqli_query($conn, "SELECT * FROM $table_name");
                
                if (!$data_result) {
                    echo "<div class='alert alert-warning'>Error fetching data for $table_name: " . mysqli_error($conn) . "</div>";
                    continue;
                }
                
                if (mysqli_num_rows($data_result) > 0) {
                    echo "<table class='table table-bordered table-striped table-hover table-sm'>";
                    echo "<thead class='table-dark'><tr>";
                    
                    // Get field information for headers
                    $fields = mysqli_fetch_fields($data_result);
                    foreach ($fields as $field) {
                        echo "<th>{$field->name}</th>";
                    }
                    echo "</tr></thead>";
                    echo "<tbody>";
                    
                    // Get data
                    while ($row = mysqli_fetch_assoc($data_result)) {
                        echo "<tr>";
                        foreach ($row as $cell) {
                            // Truncate long text for display
                            $display_text = $cell ?? 'NULL';
                            if (strlen($display_text) > 100) {
                                $display_text = substr($display_text, 0, 100) . "...";
                            }
                            echo "<td>" . htmlspecialchars($display_text) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<p class='text-muted'>No data found in this table.</p>";
                }
                echo "</div>";
            }
        }
        ?>
    </div>
</body>
</html>
