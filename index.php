<?php
require 'Api.php';
session_start();
$api = new Api();
$order_data;
$msg = '';


// Controlls of what to show:
    //login form 
    // order data
    // logout 
if(isset($_POST["action"])) {

    if($_POST["action"] === "logout") {
        // if(isset($_SESSION["username"]) && isset($_SESSION["password"])) {
        //     unset($_SESSION["username"]);
        //     unset($_SESSION["password"]);
        // }
        $api->logout();
        $test = "reset";
    }

    if($_POST["action"] === "getorder") {    
        $itemsPerPage;
        isset($_POST['itemsPerPage'])? $itemsPerPage = $_POST['itemsPerPage'] : $itemsPerPage = 50;
        $paidAtFrom;
        isset($_POST['paidAtFrom'])? $paidAtFrom = $_POST['paidAtFrom'] : '';
        $paidAtTo; 
        isset($_POST['paidAtTo'])? $paidAtTo = $_POST['paidAtTo'] : '';
        $paymentStatus;
        isset($_POST['paymentStatus'])? $paymentStatus = $_POST['paymentStatus'] : $paymentStatus = 'fullyPaid';
        
        $order_data = $api->getOrderData(1, $itemsPerPage, $paidAtFrom, $paidAtTo, $paymentStatus, null);
    
    }

    if($_POST["action"] === "login") {
       
        if(isset($_POST["username"]) && isset($_POST["password"])) {        

            if(!($api->login($_POST["username"], $_POST["password"]))) {
                $msg = '<p style="color:red">Wrong username or password</p>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    font-family: Arial, Helvetica, sans-serif;
    width: 100%;
}
form {border: 3px solid #f1f1f1;}

.form {border: 3px solid #f1f1f1;}

.wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

input[type=text], input[type=password], input[type=datetime-local],input[type=number] {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  box-sizing: border-box;
}

button {
  background-color: #04AA6D;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
}

button:hover {
  opacity: 0.8;
}

.cancelbtn {
  width: auto;
  padding: 10px 18px;
  background-color: #f44336;
}

.container {
  padding: 16px;
}

span.psw {
  float: right;
  padding-top: 16px;
}

@media screen and (max-width: 300px) {
  span.psw {
     display: block;
     float: none;
  }
  .cancelbtn {
     width: 100%;
  }
}
</style>
</head>
<body>
<?php
$html_login = '
<div class="wrap">
<h2>Login Form</h2>

    <form action="/" method="post" onSubmit="window.location.reload()">
    <div class="container">
        <label for="uname"><b>Username</b></label>
        <input type="text" placeholder="Enter Username" name="username" required>

        <label for="psw"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" name="password" required>

        <input type="hidden" name="action" value="login">
        <button type="submit">Login</button>
        '.$msg.'
        <label>
    </div>
    </form>
</div>
';


$html_reset_session = '
<div class="wrap">
    <h2>Get order</h2>
    <form action="getorder" method="post" onSubmit="window.location.reload()">
        <div class="container">

            <label for="itemsPerPage">itemsPerPage:</label>
            <input type="number" id="itemsPerPage" name="itemsPerPage" value="100">

            <label for="paidAtFrom">paidAtFrom (date and time):</label>
            <input type="datetime-local" id="paidAtFrom" name="paidAtFrom" value="2021-08-01T00:00">
            
            <label for="paidAtTo">paidAtTo (date and time):</label>
            <input type="datetime-local" id="paidAtTo" name="paidAtTo" value="2021-09-01T00:00">

            <label for="paymentStatus">paymentStatus:</label>
            <input type="text" id="paymentStatus" name="paymentStatus" value="fullyPaid" placeholder="fullyPaid">

            <input type="hidden" name="action" value="getorder">
            <button type="submit">Get order</button>
            <label>
        </div>
    </form>
</div>
<div class="wrap">
    <h2>Reset session</h2>
    <form action="logout" method="POST" onSubmit="window.location.reload()>
        <div class="container">
            <input type="hidden" name="action" value="logout">
            <button type="submit">Log out</button>
            <label>
        </div>
    </form>
</div>
';

// prints logout, order and log out form 
if(!$api->isLoggedIn()) {
    //prints login form 
    echo $html_login;
} else {
    // prints order data
    if(isset($order_data)) {
        $totalCount1 = isset($order_data['totalsCount']) ? $order_data['totalsCount'] : '';
        $totalNet = isset($order_data['sumNet']) ? $order_data['sumNet'] : '';
        $totalBruto = isset($order_data['sumBruto']) ? $order_data['sumBruto'] : '';

        echo '<div class="wrap">';
            echo '<div class="form">';
                echo '<div class="container">';
                    echo 'Number of Total Orders: ' . $totalCount1 ;
                    echo "<br>";
                    echo 'Total Amount (Net): '. $totalNet .' €';
                    echo "<br>";
                    echo 'Total Amount (Brutto): '. $totalBruto. ' €';
                    echo "<br>";
                echo '</div>';
            echo '</div>';
        echo '</div>';
    }
    // prints logout button and order form
    echo $html_reset_session;
}


?>

</body>
</html>