<?php
/*-----------------------------------------------------------------------------
// Project:     BIBLIAN 
// File:        index.php
// Dir:         /index.php
// Desc:        Basic read of the library as it stands, and nav to other pages.
//-----------------------------------------------------------------------------
*/
include('SQLFunctions.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__."/res/inc/database.php";


$databaseResult = array();
//----------------
//Old test queries
//----------------
//$databaseResult = getFromDatabase("SELECT p.ProductName, s.QuantityOnHand FROM Products as p JOIN Stock as s on s.ProductID = p.ProductID");
//$dogResult = getFromDatabase("SELECT p.ProductName, s.QuantityOnHand FROM Products as p JOIN Stock as s on s.ProductID = p.ProductID");

//----------------------
//Display Library Query
//----------------------


//$databaseResult = getFromDatabase("SELECT * FROM `Books`");

$databaseResult = getFromDatabase("SELECT b.Title, b.ISBN, b.PageCount, b.Comments, b.MSRP, b.DateAcquired, b.DateStarted, b.DateFinished, b.Progress, b.Rating FROM Books as b");


//-----------------------
//Display Purchase Query
//-----------------------
/*
$databaseResult = getFromDatabase("SELECT ph.PurchaseHeaderID, ph.PurchaseDate, CONCAT_WS(\" \", c.FirstName, c.LastName) AS 'CustomerName', e.LastName as 'SoldBy', p.ProductName, pi.Quantity, pi.UnitPrice
FROM PurchaseHeaders as ph
join PurchaseLineItems as pi
on ph.PurchaseHeaderID = pi.PurchaseHeaderID
join Customers as c
on c.CustomerID = ph.CustomerID
join Employees as e 
on e.EmployeeID = ph.EmployeeID
join Products as p
on p.ProductID = pi.ProductID");
*/

?>

<html>
<head>
<script src="res/inc/sorttable.js"></script>

<link href="https://fonts.googleapis.com/css?family=Ubuntu&display=swap" rel="stylesheet">    
<link rel="stylesheet" type="text/css" href="stylesheet.css" media="screen"/>
    
</head>
    
<body>
    <!--------------->
    <!--SideBar Div-->
    <!--------------->
    <div id='sidebar'>
        <div id='sidebarMargin'>
            <h1 id='appTitle'>Biblian</h1>
            <h1 id='pageTitle'>Home</h1>
        </div>
    </div>
    
    <!--------------------------------------------------------------------------------------------------------------------------------->
    <!--Test Pull                                                                                                                ------>
    <!--------------------------------------------------------------------------------------------------------------------------------->
    <div id="content">
        <h3>Library Test Pull</h3>
        <div id='contentMargin'>
            <table id="inventoryTable" class="sortable greyGridTable">
              <tbody>
                  <?php
                  //table headers
                  echo '<tr>';
                  foreach ($databaseResult[0] as $key => $value) {
                      echo '<td>'.$key.'</td>';
                  }
                  echo '</tr>';

                  //content
                  foreach ($databaseResult as $row) {
                      echo '<tr>';
                      foreach ($row as $key => $value) {
                          echo '<td>'.$value.'</td>';
                      }
                      echo '</tr>';
                  } 
                  ?>
              </tbody>
            </table>
        </div>

            <!--------------------------------------------------------------------------------------------------------------------------------->
            <!--INSERT Purchase Header section                                                                                           ------>
            <!--------------------------------------------------------------------------------------------------------------------------------->
        <div id='contentMargin'>
            <div id='transactionInput'>

                <h3>Add Book</h3>

                <form method='post'>
                    <!--Header-->
                    <div class=row>
                        <div class=column style='background-color:#dddddd;'>
                            <p></p>
                            Title:
                            <input type='text' name='title' value='FooBook'><br>
                            Author Last:
                            <input type='text' name='lastName' value='Howard'><br>
                            Author First:
                            <input type='text' name='firstName' value='Curly'><br>
                            Genre:
                            <input type'text' name='genre' value='barGenre'><br>
                            
                            <h4>Advanced Data</h4>
                            <!--------------------------->
                            <!--ADVANCED ADD BOOK ENTRY-->
                            <!--------------------------->
                            Author Middle 01:
                            <input type='text' name='middleName01' value=''><br>
                            Author Middle 02:
                            <input type='text' name='middleName02' value=''><br>
                            Author Suffix:
                            <input type='text' name='suffix' value=''><br>
                            
                            Rating:
                            <input type='number' name='rating' value=5.0><br>
                            DateAquried:
                            <input type='number' name='date' value='<?= time(); ?>' /><br>
                            ListPrice:
                            <input type='number' name='listPrice' value=11.99><br>
                            Comments:
                            <input type='text' name='comments' value=''><br>
                            ISBN:
                            <input type='text' name='isbn' value=''><br>
                            Page Count:
                            <input type='number' name='pageCount' value=200><br>
                            Progress:
                            <input type='number' name='progress' value=15><br>

                        
                        <input type="submit">
                    </div> <!--end row-->
                </form>
                
                
                
                
                <!------------------------------->
                <!--INSERT New Book Logic      -->
                <!------------------------------->
                <?php
                    //only execute if something is in the POST for the web page
                    if(isset($_POST["firstName"])){
                    
                    //reset databaseResult to be empty
                    $databaseResult = array();
                        
                    console_log('Console DEBUG:', 'Executed');
                    
                    //to be clear, the IDs being inserted are foriegn keys, not the PK
                    //$sqlQuery = "INSERT INTO Books(Title, ISBN, PageCount, Comments, MSRP, DateAquired, DateStarted, DateFinished, Progress, Rating) VALUES ('".$_POST['title']"',200,null,null,null,null,null,null,null,null)";
                    $sqlQuery = "INSERT INTO Books(Title, ISBN, PageCount, Comments, MSRP, DateAcquired, DateStarted, DateFinished, Progress, Rating) VALUES ('".$_POST['title']."', '".$_POST['isbn']."', '".$_POST['pageCount']."', '".$_POST['comments']."', '".$_POST['listPrice']."',null, null, null, '".$_POST['progress']."', '".$_POST['rating']."')";
//                    $sqlQuery = "INSERT INTO Books(Title, ISBN, PageCount, Comments, MSRP, DateAcquired, DateStarted, DateFinished, Progress, Rating) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7],[value-8],[value-9],[value-10],[value-11])
                        
                    //TE<MPLATE                        
                    //$sqlQuery = "INSERT INTO PurchaseLineItems(PurchaseHeaderID, ProductID, UnitPrice, Quantity, Discount) VALUES ('10','".$_POST['prodID']."', '".$_POST['prodInitPrice']."', '".$_POST['qty']."', null)";
                    
                    $databaseResult = insertToDatabase($sqlQuery);
                    print_r($sqlQuery);
                    /*    
                    $quantity = $_POST['title'];
                    
                    // UPDATE STOCK TABLE - DECREMENT
                    $sqlQuery = "UPDATE Stock
                    SET QuantityOnHand = QuantityOnHand - $quantity
                    WHERE ProductID= ".$_POST['prodID'];
                    updateDatebase($sqlQuery);
                      */  
                }
                
                
                ?>
                
                <h3>Errors Below in code</h3>
                    
                <!--------------------------------------------------------------------------------------------------------------------------------->
                <!--SELECT PurchaseHeader Data                                                                                               ------>
                <!--------------------------------------------------------------------------------------------------------------------------------->
                <?php
                    $headerDataResult = array();

                    $sqlQuery = "SELECT ph.PurchaseHeaderID, from_unixtime(ph.PurchaseDate) as 'Date', ph.CustomerName, CONCAT_WS(' ', e.FirstName, e.LastName) AS 'Sold By'
                    FROM PurchaseHeaders ph
                    join Employees e 
                    on e.EmployeeID = ph.EmployeeID
                    ";
                    $headerDataResult = getFromDatabase($sqlQuery);
                ?>
                
                <!--Display PurchaseHeader Results-->
                <!---------------------------------->
                <div id="transactionListing">
                    <table id="transactionTable" class="sortable greyGridTable">
                      <tbody>
                          <?php
                          //table headers
                          echo '<tr>';
                          foreach ($headerDataResult[0] as $key => $value) {
                              echo '<td>'.$key.'</td>';
                          }
                          echo '</tr>';

                          //content
                          foreach ($headerDataResult as $row) {
                              echo '<tr>';
                              foreach ($row as $key => $value) {
                                  echo '<td><a href="purchaseOrderDetails.php?id='.$row['PurchaseHeaderID'].'">'.$value.'</a></td>';
                              }
                              echo '</tr>';
                          } 
                          ?>
                      </tbody>
                    </table>
                </div> <!--end transactionListing-->
            </div> <!--end transactionInput-->

        </div><!--End ContentMargin-->
        
        <!--------------------------------------------------------------------------------------------------------------------------------->
        <!--Transaction Reports                                                                                                      ------>
        <!--------------------------------------------------------------------------------------------------------------------------------->
        <h3>Reports</h3>
        <!--Daily Transactions & Totals-->
        <p>Sales for the day</p>
        <?php
            $headerDataResult = array();

            $sqlQuery = "SELECT ph.PurchaseHeaderID, from_unixtime(ph.PurchaseDate) as 'Date', p.ProductName, pi.Quantity, pi.UnitPrice, pi.Discount, ph.EmployeeID
            FROM PurchaseHeaders ph
            left join PurchaseLineItems pi
            on pi.PurchaseHeaderID = ph.PurchaseHeaderID
            left join Products p
            on p.ProductID = pi.ProductID
            where ph.PurchaseDate = 1578700569 OR ph.PurchaseDate = 1578699569
            order by ph.PurchaseDate
            ";
            $headerDataResult = getFromDatabase($sqlQuery);
        ?>

        <!--Display Daily Report          -->
        <!---------------------------------->
        <div id="dailyReport">
            <table id="transactionTable" class="sortable greyGridTable">
              <tbody>
                  <?php
                  //table headers
                  echo '<tr>';
                  foreach ($headerDataResult[0] as $key => $value) {
                      echo '<td>'.$key.'</td>';
                  }
                  echo '<td>Item Total</td>';
                  echo '</tr>';

                  //content
                  $i = 0;
                  $dayTotal = 0.00;
                  foreach ($headerDataResult as $row) {
                      echo '<tr>';
                      foreach ($row as $key => $value) {
                          $i++;
                          echo '<td><a href="purchaseOrderDetails.php?id='.$row['PurchaseHeaderID'].'">'.$value.'</a></td>';
                          if($i == 7){
                              $lineTotal = $row['Quantity'] * $row['UnitPrice'];
                              echo '<td>'.$lineTotal.'</td>';
                              $dayTotal += $lineTotal;
                          }
                      }
                      $i = 0;
                      echo '</tr>';
                      
                  } 
                  echo '<tr><td colspan="8" style="text-align: right;">Day Sales Total: '.$dayTotal.'</td></tr>';
                  ?>
              </tbody>
            </table>
            
        </div> <!--end dailyReport-->

        
        <!------------------------------->
        <!--JS Scripts                 -->
        <!------------------------------->
        <script>
            //Makes our tables sortable and fancy. https://kryogenix.org/code/browser/sorttable/
            sorttable.makeSortable(document.getElementById("inventoryTable"));

            //Sidebar
            //const sidebar = document.getElementById('sidebar');
            const buttons = document.querySelectorAll("button");

            buttons.forEach(button =>
              button.addEventListener("click", _ => {
                document.getElementById("sidebar").classList.toggle("collapsed");
              })
            );

        </script>
    </div>
    */
</body>	

	
</html>