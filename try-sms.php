<?php 

$ch = curl_init();
$parameters = array(
    'apikey' => '0e1eb241cf70f66127f683cfb8a90e34', //Your API KEY
    'number' => '09566320135',
    'message' => 'I just sent my first message with Semaphore',
    'sendername' => 'CSH'
);
curl_setopt( $ch, CURLOPT_URL,'https://semaphore.co/api/v4/messages' );
curl_setopt( $ch, CURLOPT_POST, 1 );

//Send the parameters set above with the request
curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $parameters ) );

// Receive response from server
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$output = curl_exec( $ch );
curl_close ($ch);

//Show the server response
echo $output;

?>