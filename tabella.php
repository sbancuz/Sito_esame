<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Get the $_GET['table'], check if it's in the tables and set the title -->
    <?php
    include "./functions.php";
    $tables = sendRequest("schema");
    array_push($tables, "consumption", "production");
    // Check if the get table actually exists, otherwise use the default one
    $currentTable = '';
    if (isset($_GET['table']) && in_array($_GET['table'], $tables)) {
        $currentTable = $_GET['table'];
    } else {
        $currentTable = $tables[0];
    }

    print("<title>$currentTable - AllMyData</title>");
    ?>

    <!-- Set favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/apple-touch-icon.png">

    <!-- Bootstrap + deps -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/b-1.6.2/b-colvis-1.6.2/cr-1.5.2/r-2.2.5/sc-2.0.2/datatables.min.css" />

    <!-- Datatables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/b-1.6.2/b-colvis-1.6.2/cr-1.5.2/fh-3.1.7/r-2.2.5/sc-2.0.2/sp-1.1.1/datatables.min.css" />
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.21/b-1.6.2/b-colvis-1.6.2/cr-1.5.2/fh-3.1.7/r-2.2.5/sc-2.0.2/sp-1.1.1/datatables.min.js"></script>

    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="img/batteryfav.png">

    <style>
        label {
            color: white;
        }

        #table_info {
            color: white;
        }

        .paginate_button a {
            background-color: #2a2a2e;
            border: 0px;
        }

        .paginate_button {
            border: 1px solid #343a40;
        }

        .paginate_button.disabled a {
            background-color: #2a2a2e !important;
            border: 0px !important;
        }

        .paginate_button.disabled {
            border: 1px solid #343a40 !important;
        }

        .modal-body {
            position: relative;
            overflow: hidden;
            max-height: 400px;
            padding: 15px;
        }

        body .modal-dialog {
            max-width: 100%;
            width: auto !important;
            display: inline-block;
        }

        .modal {
            z-index: -1;
            display: flex !important;
            justify-content: center;
            align-items: center;
        }

        .modal-open .modal {
            text-align: center;
            z-index: 1050;
        }

        #table-select {
            background-color: #3e444a;
        }

        #table-select a {
            color: white;
        }

        #table-select a:hover {
            background-color: #2a2a2e;
        }

        #table-select-button {
            background-color: #343a40;
        }

        .form-control {
            background-color: #343a40;
            color: white;
            border: 0px;
        }

        #delButtons::before,
        #delButtons::after {
            display: none !important;
        }

        .btn-secondary{
            color: white;
        }

    </style>
</head>

<body>
    <?php
    // If something posted, create/update a new element
    if (isset($_POST['action'])) {
        if ($_POST['action'] == "add") {
            $result = sendRequest("create", $currentTable, $_POST)['message'];
        } else if ($_POST['action'] == "update") {
            $result = sendRequest("update", $currentTable, $_POST)['message'];
        }
        echo '
            <script>
                alert("' . $result . '");
            </script>
        ';
    }
    // importing the data for the table
    if ($currentTable == "consumption" || $currentTable == "production") {
        $rows = sendRequest($currentTable);
    } else {
        $rows = sendRequest("read", $currentTable);
    }
    $table_cols = array_keys($rows[0]);

    ?>
    <div class="w-100 h-100 p-3 mx-auto flex-column">

        <header class="masthead mb-auto">
            <div class="inner">

                <div>
                    <h3 class="masthead-brand" style="margin-left: 25vw;">World monitor</h3>
                    <img src="./img/battery.png" class="img">
                </div>
                <nav class="nav nav-masthead justify-content-center">

                    <div class="d-flex flex-grow-1" style="margin-right: 25vw;">

                        <a class="nav-link" href="index.html">Home</a>
                        <a class="nav-link" href="map.html">Map</a>
                        <a class="nav-link" href="about.html">About</a>
                        <a class="nav-link active" href="tabella.php">Table</a>

                        <!--<div class="container" onclick="myFunction(this)">
                            <div class="bar1"></div>
                            <div class="bar2"></div>
                            <div class="bar3"></div>-->
                    </div>

                </nav>
            </div>
        </header>

        <!-- Modal to add DB data -->
        <div class="modal fade" id="Modal" tabindex="-1" role="dialog" aria-labelledby="delmodal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content" style="background-color: #333">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dataModalTitle">Add data</h5>
                    </div>
                    <div class="modal-body mx-auto">
                        <?php
                        echo "<form action='./tabella.php?table=" . $currentTable . "'method='post'>";
                        foreach ($table_cols as $col) {
                            echo '<p>';
                            if ($col == "ID" || ($col == "ISO_char" && $currentTable != "countries")) {
                                echo "<label style='float: left;width:50%;'>" . $col . "</label>";
                                echo "<input class='provain' readOnly='true' type='text' id='{$col}' name='{$col}' value='' style='float:right;width:50%;'><br>";
                            } else {
                                echo "<label style='float: left;width:50%;'>" . $col . "</label>";
                                echo "<input class='provain' type='text' id='{$col}' name='{$col}' value='' style='float:right;width:50%;'><br>";
                            }
                            echo '</p>';
                        }
                        echo '                    
                        </div>
                        <div class="modal-footer" >
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';

                        echo "<input id='submit' type='hidden' name='action' value='Add'>";

                        echo "<input type='submit' class='btn btn-secondary'>";
                        echo "</form>";
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- The container that contains the table + the controls. Display:none to avoid a FOUC -->
        <div id="table_container" class="container-fluid mt-3 table-responsive overflow-auto" style="display: none; width:75%;">
            <div class="row" style="padding: 5px 0px;">
                <div class="col">
                    <!-- Choose table dropdown. Every item in the dropdown is a href to the page with ?table=tablename. -->
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="table-select-button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Choose table
                        </button>
                        <div class="dropdown-menu" id="table-select" aria-labelledby="dropdownMenuButton">
                            <?php
                            foreach ($tables as $table) {
                                print('<a class="dropdown-item" href="./tabella.php?table=' . $table . '">' . $table . '</a>');
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- The actual table -->
            <table id="table" class="table table-bordered table-striped table-dark table-hover ">
                <thead>
                    <tr>
                        <?php
                        // Add the column that contains the red '-' buttons
                        print('<th id="delButtons"><button id="addButton" type="button" class="btn btn-success" data-type="add" data-toggle="modal" data-target="#add">+</button></th>');
                        // Now add the table columns
                        foreach ($table_cols as $col) {
                            print('<th>' . $col . '</th>');
                        }

                        // Close table head and open table body
                        print('</tr>
                        </thead>
                        <tbody id="tbody">');


                        // Fill the table
                        $rowNum = 0;
                        foreach ($rows as $row) {
                            print('<tr>');
                            print('<td><div class="row" style="width:100px; padding-left:10px">
                                <button type="button" id="delButton" class="btn btn-danger" data-type="delete" onClick="del(' . $rowNum . ')" data-target="#delModal">-</button>
                                <button type="button" id="upButton" class="btn btn-info" data-type="update" onClick="update(' . $rowNum . ')" data-target="#dataModal">&#x2699</button>
                                </div></td>');
                            foreach ($row as $index => $value) {
                                print('<td>' . utf8_encode($value) . '</td>');
                            }
                            print('</tr>');
                            $rowNum += 1;
                        }

                        ?>
                        </tbody>
            </table>
        </div>

        <script>
            // When the document is ready, load DataTables and then show the table
            $(document).ready(function() {
                $('#table').DataTable({
                    "columnDefs": [{
                        "orderable": false,
                        "targets": [0]
                    }],
                    colReorder: {
                        fixedColumnsLeft: 1
                    }
                });
                document.getElementById('table_container').style.display = 'block';
            });
            $('#Modal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
            })

            function del(id) {
                var scelta = confirm("Are you sure?");
                if (scelta) {
                    var table = $('#table').DataTable();
                    var row = table.row(id).data();
                    var json = {
                        "ID": row[1]
                    }
                    var settings = {
                        "url": "https://lucabancale.netsons.org/api/product/delete.php?table=<?php echo $currentTable; ?>",
                        "method": "POST",
                        "timeout": 0,
                        "headers": {
                            "Content-Type": "text/plain"
                        },
                        "data": JSON.stringify(json)
                    };

                    jQuery.ajax(settings).done(function(response) {
                        alert(response.message);
                        // Simulate an HTTP redirect reloading the page without accidentaly posting something 
                        window.location.replace(window.location.href);
                    }).fail(function(response) {
                        alert(response.message);
                        // Simulate an HTTP redirect reloading the page without accidentaly posting something 
                        window.location.replace(window.location.href);
                    });

                    //location.reload();
                }
            }
            $('#addButton').click(function() {
                var inputs = document.getElementsByClassName("provain");
                for (var i = 0; i < inputs.length; i++) {
                    inputs[i].value = "";
                }
                document.getElementById("dataModalTitle").innerHTML = "Add data";
                document.getElementById("submit").value = "add";

                $('#Modal').modal({
                    show: true
                });
            })

            function update(id) {
                var table = $('#table').DataTable();
                var row = table.row(id).data();

                var inputs = document.getElementsByClassName("provain");

                for (var i = 1; i < row.length; i++) {
                    inputs[i - 1].value = row[i];
                }

                document.getElementById("dataModalTitle").innerHTML = "Update data";
                document.getElementById("submit").value = "update";

                console.log(inputs)
                $('#Modal').modal({
                    show: true
                });
            }
        </script>

        <!-- Footer -->
        <footer class="mastfoot mt-auto" style="text-align: center">
            <!-- Copyright -->
            <div class="inner">
                <!--TODO-->
                <h5>A cura di <a href="https://github.com/lbanca01?tab=stars">Luca Bancale</a></h5>
            </div>
            <!-- Copyright -->

        </footer>
        <!-- Footer -->
        <script>
            function myFunction(x) {
                x.classList.toggle("change");
            }
        </script>
    </div>
</body>

</html>