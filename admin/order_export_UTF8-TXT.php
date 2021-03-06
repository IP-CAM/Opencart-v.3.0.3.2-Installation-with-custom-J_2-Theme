<?php
error_reporting(E_ERROR);
/* =============================================================
/   * OpenCart 2.x/3.x Order+ Order Options Excel Export Tool "UTF-8 TXT" version 5
	* Developed by Daniel Brooke Peig (daniel@danbp.org)
	*
	* http://www.danbp.org
	*
	*  Copyright (C) 2017  Daniel Brooke Peig
	*
	* This software is distributed under the MIT License.
	* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), * to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
	*
	* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
	*
	* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	*
	*
/* =============================================================*/
/*
*
*	INTRODUCTION
*
*	This script should work in all OpenCart 2.x installations because it extracts the data directly from the OpenCart Database and does not rely on any OpenCart directory files.
*	The script just dumps readable order data in an Excel file, you may later use Excel functions to auto-format the data according to your needs.
*
*   THE "UTF-8 TXT" VERSION FOR ADVANCED USERS
*	
*	This version should be used when the characters appear invalid in the Excel export. It will export a UTF-8 text file that can be imported in Excel.
*	You need to know the data encoding from the database to convert properly to the UTF-8 format (see instruction #2)
* 
*	INSTRUCTIONS
*	
*	1. Modify the script configuration below to your database access information and set a PASSWORD. The password will protect the script from outside access and should be different than the DB password.
*	2. Modify the DB_CHARSET configuration to the charset from your database, usually UTF-8 but it could be ISO-8859-1 to ISO-8859-5 or others...
*	3. Place the modified script in any accessible (but safe) folder in your server, for example, the OpenCart /admin directory.
*	4. Access the script by using the following URL. Remember to replace YOURPASSWORD with the password you set in the configuration.
*		URL: https://www.yourserver.com/yourfolder/admin/order_export.php?pw=YOURPASSWORD
*	5. If everything is OK you will be prompted to download the Text file that should be opened in Excel (delimited text file with semicolon ';').
*	6. Each line of the Excel file represents one order item or item option. You may group options for the same item by using the column product_item.

*/


//Script Configuration
define ("DB_HOST", "localhost"); //DB host address
define ("DB_USER", "usrTelRead"); //DB user name
define ("DB_PASS","hg56agag1937HFTT"); //DB password
define ("DB_NAME","telas_testing_template"); //DB database name
define ("PASSWORD", "hg34tftfqqpp"); //Script access password (will be used to prevent unauthorized access to the data). Please use a different password than the DB.
define ("DB_CHARSET", "UTF-8"); //Database data source charset (will be converted to UTF-8 for the output): ISO-8859-1, UTF-8,...
define ("PREFIX", "oc_"); //Table name prefix (if any). Example: "oc_", "opencart_", etc...
define ("FILENAME", "order_data"); //Export default filename


//SQL Query, customize if if you need any more (or less) fields
define ("SQL","
SELECT
	`".PREFIX."order`.order_id,
	`".PREFIX."order`.invoice_no,
	`".PREFIX."order`.store_name,
	`".PREFIX."order`.customer_id,
	`".PREFIX."order`.customer_group_id,
	firstname,
	lastname,
	email,
	telephone,
	`".PREFIX."order`.date_added,
	`".PREFIX."order`.date_modified,
	`".PREFIX."order`.total AS order_total,
	currency_code,
	".PREFIX."order_status.name AS status,
	".PREFIX."order_product.product_id AS product_item,
	".PREFIX."order_product.name AS product_name,
	".PREFIX."order_product.model AS product_model,
	".PREFIX."order_product.quantity AS product_quantity,
	".PREFIX."order_option.order_product_id AS option_product,
	".PREFIX."order_option.product_option_id AS option_id,
	".PREFIX."order_option.name AS option_name,
	".PREFIX."order_option.value AS option_value,
	".PREFIX."order_product.total AS product_total,
	".PREFIX."order_product.tax,
	custom_field,
	payment_method,
	payment_code,
	payment_firstname,
	payment_lastname,
	payment_company,
	payment_address_1,
	payment_address_2,
	payment_city,
	payment_zone,
	payment_postcode,
	payment_country,
	payment_custom_field,
	shipping_method,
	shipping_code,
	shipping_firstname,
	shipping_lastname,
	shipping_company,
	shipping_address_1,
	shipping_address_2,
	shipping_city,
	shipping_zone,
	shipping_postcode,
	shipping_country,
	shipping_custom_field,
	comment
FROM `".PREFIX."order` 
LEFT JOIN ".PREFIX."order_product ON `".PREFIX."order`.order_id = `".PREFIX."order_product`.order_id
LEFT JOIN ".PREFIX."order_status ON `".PREFIX."order`.order_status_id = `".PREFIX."order_status`.order_status_id
LEFT JOIN ".PREFIX."order_option ON `".PREFIX."order_product`.order_product_id = ".PREFIX."order_option.order_product_id
WHERE `".PREFIX."order_status`.order_status_id > 0
ORDER BY
	`".PREFIX."order`.order_id,
	".PREFIX."order_product.product_id,
	".PREFIX."order_option.product_option_id ASC
");


if($_GET["pw"]==PASSWORD){

	//Connect to the database and fetch the data
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die("DB: Couldn't make connection. Please check the database configurations.");
	$setSql = SQL;
	$setRec = mysqli_query($link, $setSql);
	
	//Fetch the column names
	$columns = mysqli_fetch_fields($setRec);
	foreach($columns as $column){
		$setMainHeader .= $column->name.";";
	}

	while($rec = mysqli_fetch_row($setRec))  {
	  $rowLine = '';
	  foreach($rec as $value)       {
		if(!isset($value) || $value == "")  {
		  $value = ";";
		}   else  {
	//Escape all the special characters
		  $value = mb_convert_encoding($value, "UTF-8", DB_CHARSET);
		  $value = str_replace("\n", " ", $value);
		  $value = str_replace(";", ",", $value);
		  $value = strip_tags(str_replace('"', '""', $value));
		  $value = '"'.$value . '"' . ";";
		}
		$rowLine .= $value;
	  }
	  $setData .= trim($rowLine)."\n";
	}
	  $setData = str_replace("\r", "", $setData);
	if ($setData == "") {
	  $setData = "\nNo matching records found\n";
	}

	//Download headers
	header('Content-Type: text/plain; charset=utf-8');
	header("Content-Disposition: attachment; filename=".FILENAME."-".date("Y_m_d-Hi_s").".txt");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo "\xEF\xBB\xBF"; //UTF-8 BOM

	//Print the table rows as an Excel row with the column name as a header
	echo ucwords($setMainHeader)."\n".$setData."\n";
}
//Message to display in case of wrong access password
else {
	$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
	echo "Invalid password! Remember to write the URL properly and include your password:<BR>".(isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]".$uri_parts[0]."?pw=your_password";
}
?>
