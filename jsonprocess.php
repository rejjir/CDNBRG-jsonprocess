<?php
$files = scandir('JSONinput');
$total = count($files);
$filecount = $total - 2; // Scandir returns an extra 2 files for "." and ".."
echo "$filecount file(s) found \n";

for ($i = 2; $i < $total; $i++)
	{
	$f = $i - 1;
	echo "running file $f \n";

	// Get JSON input
	$input = file_get_contents("JSONinput/$files[$i]");

	// Convert it into array
	$vars = json_decode($input, true);

	// Number of order lines
	$orders = count($vars["vmiorders"]["customer"][0]["shipto"][0]["orderline"]);

	// Generate header line
	$outputheader = "H^VMImail"
	 . "^" . $vars["vmiorders"]["customer"][0]["shipto"][0]["custref"] 	// Shipto-custref
	 . "^" . date("Ymd") 							// Date
	 . "^" . date("H:i") 							// Time
	 . "^" . $orders 							// Number of orders
	 . "^" . $vars["vmiorders"]["customer"][0]["-id"] 			// Customer-id
	 . "^" . $vars["vmiorders"]["customer"][0]["shipto"][0]["-id"] 		// Shipto-id
	 . "^" . $vars["vmiorders"]["customer"][0]["shipto"][0]["emailto"] 	// Email
	 . "," . $vars["vmiorders"]["customer"][0]["shipto"][0]["emailcc"];	// CC

	// Generate order lines
	$output = "";
	for ($j = 0; $j < $orders; $j++)
		{
		$orderline[$j] = "D"
		 . "^" . $vars["vmiorders"]["customer"][0]["shipto"][0]["orderline"][$j]["prod"]	// Product
		 . "^" . $vars["vmiorders"]["customer"][0]["shipto"][0]["orderline"][$j]["qty"]		// Quantity
		 . "^";
		$output = $output . $orderline[$j];
		}

	$output = $output . $outputheader . "\n";

	// Generate output file name
	$outfile = explode('.', $files[$i]);
	$location = "TXToutput/$outfile[0].txt";

	// Place it into output folder
	file_put_contents($location, $output);

	// Move processed JSON out of incoming folder to archive folder
	rename("JSONinput/$files[$i]", "JSONarchived/$files[$i]");
	echo "Output saved to $location \n";
	}

echo "Complete! \n";
?>
