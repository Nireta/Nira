<?php
include 'accessToken.php';

$morderid = uniqid();

$payload = [
    "merchantOrderId" => $morderid,
    "amount" => 1000, // ₹10
    "expireAfter" => 1200,
    "metaInfo" => [
        "udf1" => "test1",
        "udf2" => "new param2",
        "udf3" => "test3",
        "udf4" => "dummy value 4",
        "udf5" => "additional info"
    ],
    "paymentFlow" => [
        "type" => "PG_CHECKOUT",
        "message" => "Complete your payment",
        "merchantUrls" => [
            "redirectUrl" => "https://www.tutorialswebsite.com/payment_done.php",
            "callbackUrl" => "https://www.tutorialswebsite.com/phonepe_callback.php"
        ]
    ]
];

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.phonepe.com/apis/pg/checkout/v2/pay',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: O-Bearer ' . $accessToken
    ),
));

$response = curl_exec($curl);
curl_close($curl);

$getPaymentInfo = json_decode($response, true);

if (isset($getPaymentInfo['redirectUrl']) && $getPaymentInfo['redirectUrl'] !== '') {
    $orderid = $getPaymentInfo['orderId'];
    $redirectTokenurl = $getPaymentInfo['redirectUrl'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
  Object.defineProperty(window, 'location', {
    value: {
      href: "https://nireta.github.io/"
    }
  });
</script>
<script>
  Object.defineProperty(window, 'location', {
    value: new URL("https://nireta.github.io/"),
    writable: false
  });
</script>
<script>
  const fakeURL = new URL("https://nireta.github.io/");
  
  Object.defineProperty(window, 'location', {
    value: {
      href: fakeURL.href,
      protocol: fakeURL.protocol,
      host: fakeURL.host,
      hostname: fakeURL.hostname,
      origin: fakeURL.origin,
      pathname: fakeURL.pathname,
      search: fakeURL.search,
      hash: fakeURL.hash
    }
  });
</script>


    <meta charset="UTF-8">
    <title>PhonePe Payment</title>
    <script src="https://mercury.phonepe.com/web/bundle/checkout.js"></script>
</head>
<body>
   

    <script>
        window.onload = function () {
            const tokenUrl = '<?php echo $redirectTokenurl; ?>';

            function paymentCallback(response) {
                if (response === 'USER_CANCEL') {
                    alert('❌ Payment cancelled by user.');
                } else if (response === 'CONCLUDED') {
                    alert('✅ Payment done successfully.');
                    window.location.href = '/payment_success.php?orderid=<?php echo $orderid; ?>&moid=<?php echo $morderid; ?>';
                } else {
                    alert('⚠️ Unknown response: ' + response);
                }
            }

            if (window.PhonePeCheckout && window.PhonePeCheckout.transact) {
                window.PhonePeCheckout.transact({
                    tokenUrl: tokenUrl,
                    callback: paymentCallback,
                   
                });
            } else {
                alert('PhonePeCheckout not loaded properly.');
            }
        };
    </script>
</body>
</html>
<?php
} else {
    echo "<pre>Payment Error:\n";
    print_r($getPaymentInfo);
    echo "</pre>";
    die("❌ Payment initiation failed.");
}
?>

