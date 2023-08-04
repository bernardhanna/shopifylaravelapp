<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-04-20 14:14:19
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-04-20 14:20:03
 */
// Connect to the MySQL database
$servername = "localhost";
$username = "ipl";
$password = "t0_63lTi8";
$dbname = "ipl";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$orders_json = file_get_contents('json/orders.json');
// Read the orders.json file
$orders = json_decode($orders_json, true);

// create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_reference VARCHAR(50) NOT NULL,
  customer_title VARCHAR(50) NOT NULL,
  customer_forename VARCHAR(50) NOT NULL,
  customer_surname VARCHAR(50) NOT NULL,
  customer_house_name_number VARCHAR(50) NOT NULL,
  customer_line_1 VARCHAR(50) NOT NULL,
  customer_line_2 VARCHAR(50) NOT NULL,
  customer_line_3 VARCHAR(50) NOT NULL,
  customer_county VARCHAR(50) NOT NULL,
  customer_postcode VARCHAR(10) NOT NULL,
  customer_telephone VARCHAR(20) NOT NULL,
  customer_email VARCHAR(100) NOT NULL,
  order_line VARCHAR(50) NOT NULL,
  despatch_date VARCHAR(50) NOT NULL,
  carrier_code VARCHAR(50) NOT NULL,
  carrier_name VARCHAR(50) NOT NULL,
  tracking_number VARCHAR(50),
  ship_method VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt !== false) {
    if (mysqli_stmt_execute($stmt)) {
        echo "Orders table created successfully\n";
    } else {
        echo "Error creating orders table: " . mysqli_stmt_error($stmt) . "\n";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing orders table creation statement: " . mysqli_error($conn) . "\n";
}

// create product_codes table
$sql = "CREATE TABLE IF NOT EXISTS product_codes (
  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT(6) UNSIGNED NOT NULL,
  code VARCHAR(50) NOT NULL,
  quantity INT(6) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id)
)";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt !== false) {
    if (mysqli_stmt_execute($stmt)) {
        echo "Order products table created successfully\n";
    } else {
        echo "Error creating order products table: " . mysqli_stmt_error($stmt) . "\n";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing order products table creation statement: " . mysqli_error($conn) . "\n";
}

// Initialize an empty array for Shopify orders
// create orders table
foreach ($orders as $order) {
    // check if order exists before inserting
    $sql = "SELECT id FROM orders WHERE order_reference=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $order['order_reference']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        echo "Order with reference ".$order['order_reference']." already exists\n";
        continue;
    }
  // include order into orders table
  $sql = "INSERT INTO orders (
  order_reference,
  customer_title,
  customer_forename,
  customer_surname,
  customer_house_name_number,
  customer_line_1,
  customer_line_2,
  customer_line_3,
  customer_county,
  customer_postcode,
  customer_telephone,
  customer_email,
  order_line,
  despatch_date,
  carrier_code,
  carrier_name,
  tracking_number,
  ship_method
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ssssssssssssssssss",
    $order['order_reference'],
    $order['customer']['title'],
    $order['customer']['forename'],
    $order['customer']['surname'],
    $order['customer']['address']['house_name_number'],
    $order['customer']['address']['line_1'],
    $order['customer']['address']['line_2'],
    $order['customer']['address']['line_3'],
    $order['customer']['address']['county'],
    $order['customer']['address']['postcode'],
    $order['customer']['telephone'],
    $order['customer']['email'],
    $order['order_line'],
    $order['despatch_date'],
    $order['carrier_code'],
    $order['carrier_name'],
    $order['tracking_number'],
    $order['ship_method']);
  if (mysqli_stmt_execute($stmt)) {
      $order_id = mysqli_insert_id($conn); // get the ID of the newly inserted order
      echo "Order inserted successfully\n";
  } else {
      echo "Error inserting order: " . mysqli_error($conn) . "\n";
  }
  // include product_codes  into product_codes table
  // insert products into product_codes table
foreach ($order['product_codes'] as $product) {
      $sql = "INSERT INTO product_codes (
        order_id,
        code,
        quantity) VALUES (?, ?, ?)";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "iss", $order_id, $product['code'], $product['quantity']);
      if (mysqli_stmt_execute($stmt)) {
          echo "Product inserted successfully\n";
      } else {
          echo "Error inserting product: " . mysqli_error($conn) . "\n";
      }
  }
}

// create the data for the metafields
$order_reference = array(
    'namespace' => 'global',
    'key' => 'order_reference',
    'value' =>  $order['order_reference'],
    'type' => 'string'
);

$telephone_number = array(
    'namespace' => 'global',
    'key' => 'telephone',
    'value' => $order['customer']['telephone'],
    'type' => 'string' // just using a string for now
);

$despatch_number = array(
    'namespace' => 'global',
    'key' => 'despatch_number',
    'value' => $order['despatch_date'],
    'type' => 'string' // just using a string for now
);

$despatch_date = array(
    'namespace' => 'global',
    'key' => 'despatch_date',
    'value' => $order['despatch_date'],
    'type' => 'string' // Again, just using a string for now
);

$carrier_code = array(
    'namespace' => 'global',
    'key' => 'carrier_code',
    'value' => $order['carrier_code'],
    'type' => 'string'
);

$carrier_name = array(
    'namespace' => 'global',
    'key' => 'carrier_name',
    'value' => $order['carrier_name'],
    'type' => 'string'
);

$tracking_number = array(
    'namespace' => 'global',
    'key' => 'tracking_number',
    'value' => $order['ship_method'],
    'type' => 'string' // just using a string for now
);

$ship_method = array(
    'namespace' => 'global',
    'key' => 'ship_method',
    'value' =>  $order['ship_method'],
    'type' => 'string'
);

$product_codes = array(
    'namespace' => 'global',
    'key' => 'product_codes',
    'value' => json_encode($order['product_codes']),
    'type' => 'json'
);

// create an array with all the metafields
$metafields = array(
    $order_reference,
    $telephone_number,
    $despatch_number,
    $despatch_date,
    $carrier_code,
    $tracking_number,
    $ship_method,
    $carrier_name,
    $product_codes
);

$password = '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';

// create the cURL request for each metafield
foreach ($metafields as $metafield_data) {
    $url = "https://ipltester.myshopify.com/admin/api/2023-04/metafields.json";
    $headers = array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $password"
    );
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode(array("metafield" => $metafield_data)),
        CURLOPT_HTTPHEADER => $headers
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $response = json_decode($response, true);
    }
}


$endpoint = 'https://ipltester.myshopify.com/admin/api/2023-04/orders.json?status=any';
$api_key = 'da0ad3091908bd22bcd8f4ac6df794fd';
$password = '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';

$headers = array(
	"Content-Type: application/json",
	"X-Shopify-Access-Token: $password"
);
$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => $endpoint,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => $headers
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
if ($err) {
	echo "cURL Error #:" . $err;
} else {
	$response = json_decode($response, true);
}

// Check status of order between internal orders and shop orders
function check_order($shop_orders, $internal_order)
{
	if (isset($shop_orders) && is_array($shop_orders)) {
		foreach ($shop_orders as $key => $shop_order) {

			$customer = $shop_order['customer'];
			if ($customer['email'] == $internal_order['customer']['email'])
				return true;
		}
	}

	return false;
}


// Loop through each order
foreach ($orders as $order) {
	$order_data = array(
    'order' => array(
        'order_reference' => $order_reference,
        'despatch_number' => $despatch_number,
        'carrier_code' =>  $carrier_code,
        'carrier_name' =>  $carrier_name,
        'tracking_number' => $tracking_number,
        'ship-method' => $ship_method ,
        'financial_status' => 'paid', //This may need to change and is just an example, This is a required field
        'fulfillment_status' => 'unfulfilled', //This may need to change and is just an example, This is a required field
        'customer' => array(
            'first_name' => $order['customer']['forename'],
            'last_name' => $order['customer']['surname'],
            'email' => $order['customer']['email'],
            'addresses' => array(
                array(
                    'address1' => $order['customer']['address']['house_name_number'],
                    'address2' => $order['customer']['address']['line_1'],
                    'address3' => $order['customer']['address']['line_2'],
                    'city' => $order['customer']['address']['line_3'],
                    'telephone' => $telephone_number,
                    'zip' => $order['customer']['address']['postcode'],
                )
            )
        ),
        'billing_address' => array(
            'first_name' => $order['customer']['forename'],
            'last_name' => $order['customer']['surname'],
            'address1' => $order['customer']['address']['house_name_number'],
            'address2' => $order['customer']['address']['line_1'],
            'address3' => $order['customer']['address']['line_2'],
            'city' => $order['customer']['address']['line_3'],
            'telephone' => $telephone_number,
            'province'=> 'CA',
            'country'=> 'US',
            'zip' => $order['customer']['address']['postcode'],
            'phone'=> '555-555-5555'
        ),
        'shipping_address' => array(
            'first_name' => $order['customer']['forename'],
            'last_name' => $order['customer']['surname'],
            'address1' => $order['customer']['address']['house_name_number'],
            'address2' => $order['customer']['address']['line_1'],
            'address3' => $order['customer']['address']['line_2'],
            'city' => $order['customer']['address']['line_3'],
            'telephone' => $telephone_number,
            'province'=> 'CA',
            'country'=> 'US',
            'zip' => $order['customer']['address']['postcode'],
            'phone'=> '555-555-5555'
        ),
        'line_items' => array(
            array(
                'title' => 'SWSM order', //This needs to change and is just an example, This is a required field
                'price' => 129.99, //This needs to change and is just an example, This is a required field
                'quantity' => 1 //This needs to change and is just an example, This is a required field
            )
        ),
        'product_codes' => $product_codes,
        )

);
	// Check orders of shop
	if (!check_order($response['orders'], $order)) {
		echo "Send order now...";

        // SET ENDPOINTS
    $endpoint = 'https://ipltester.myshopify.com/admin/api/2023-04/orders.json';
    $password = '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';
    $headers = array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: $password"
    );

    // convert order data to JSON
    $order_json = json_encode($order_data);
    // set cURL options
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $order_json);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // check for cURL errors
    if (curl_errno($ch)) {
        $error_msg = "cURL error: " . curl_error($ch);
        curl_close($ch);

        // log error and stop execution
        error_log($error_msg);
        die("An error occurred while creating the order. Please try again later.");
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // close cURL connection
    curl_close($ch);

    // check response status
    if ($http_code == 201) {
        echo "Order created successfully\n";
    } else {
        // log error and stop execution
        $error_msg = "Error creating order: " . $response;
        error_log($error_msg);
        echo($error_msg);

        die("An error occurred while creating the order. Please try again later.");
    }


	}
}

// Close the database connection
if (isset($mysqli))
    $mysqli->close();
