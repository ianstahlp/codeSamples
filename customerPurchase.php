<?php 
    require "class.ezpdf.php";
    require "db.inc";

    //Do the querying to produce the customer report
    $query = "SELECT customer.cust_id, surname, firstname, 
                    SUM(qty), SUM(price), MAX(order_id)
                    FROM customer, items
                    WHERE customer.cust_id = items.cust_id
                    GROUP BY customer.cust_id";
                    
    if (!($connection = @ mysql_connect($hostName, $username, $password)))
        die("Could not connect to database");

    if (!(mysql_selectdb($databaseName, $connection)))
        showerror();
    
    if(!($result = @ mysql_query($query, $connection)))
        showerror();
    
        //Create a new PDF document

    $doc =& new Cezpdf();

    //Use Helvetica font for fancy trendiness

    $doc->selectFont('./fonts/Helevetica.afm');

    //Number pages

    $doc->ezStartPageNumbers(320, 15, 8);

    //set up running totals and an empty array for the output

    $counter = 0;
    $totalOrders = 0;
    $totalBottles = 0;
    $totalAmount = 0;

    // Get the query rows, and put them in the table 
    while ($row = mysql_fetch_array($result)) {
        // Counts the total number of rows output

        $counter++;

        // Add current query row to the array of custmer information

        $table[] = array(
            "Customer #"=>$row["cust_id"],
            "Name"=> "{$row['surname']}, {$row['firstname']}",
            "Orders Placed"=> $row["MAX(order_id)"],
            "Total Bottles"=> $row["SUM(qty)"],
            "Total Amount"=> "\$($row['SUM(price)']}");
             
        // Update Running tables 
        $totalOrders += $row["MAX(order_id)"];
        $totalBottles += $row["SUM(qty)"];
        $totalAmount += $row["SUM(price)"];
    }

    //Todays date is used in the table deading 

    $date = date("d M Y");

    //Right-justify the numeric columns

    $options = array("cols" =>
                        array("Total Amount" =>
                            array("justification" => "right"),
                            "Total Bottles" =>
                            array("justification" => "right"),
                            "OrdersPlaced" =>
                            array("justification" => "right")));

    // Output the table with a deading 

    $doc->ezTable($table, "", "Customer Order Repost for {$date}"),

    $doc->ezSetDy(-10);

    // Show totals

    $doc-> ezText("Total customers: {$counter}");
    $doc-> ezText("Total orders: {$totalOrders}");
    $doc-> ezText("Total bottles: {$totalBottles}");
    $doc-> ezText("Total amount: {$totalAmount}");

    //Output the document
    $doc-> ezStream();
