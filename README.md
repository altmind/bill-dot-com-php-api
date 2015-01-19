# bill-dot-com-php-api
PHP Bill.com API from http://developer.bill.com/api-documentation/code-samples/php/ nicely packaged.

Bill.com is software product that allows to manage your company financial flows, organize interaction with suppliers and clients and track your expenses and payables .

This package is heavily influenced by official code samples, but is not limited to code in official sample.

##Installation
This package is "composer"-ed. To use it, just add "altmind/bill-dot-com-php-api": "dev-master" in composer.json deps.

Packagist page: https://packagist.org/packages/altmind/bill-dot-com-php-api

##Usage Examples
```php
// see APIDoc http://developer.bill.com/api-documentation/api/chart-accounts/

$api = new \BillComApi\BillCom("YOUR_DEV_KEY","YOUR_PASS","YOUR_USERNAME"/*, "HOSTNAME" */);
try{

  // login is optional, it will be performed automatically
  $api->login();

  // list organizations associated with account, also optional. by default, we'll query for orgs and use first.
  echo json_encode($api->list_orgs()).PHP_EOL;
  //Response: [{"orgId":"00123ABCDEFGHIGK4abc","orgName":"ThreadMeUp"}]

  /* Listable eneitties:
  - ApprovalPolicy
  - ApprovalPolicyApprover
  - BankAccount
  - Bill
  - SentPay
  - ChartOfAccount
  - ActgClass
  - Customer
  - CustomerBankAccount
  - CustomerContact
  - Department
  - Employee
  - Invoice
  - ReceivedPay
  - Item
  - Job
  - Location
  - MoneyMovement
  - Organization
  - RecurringBill
  - User
  - Vendor
  - VendorBankAccount
  - VendorCredit
  */
  echo json_encode($api->get_list('Bill')).PHP_EOL;
  //Response: [{"entity":"Bill","id":"00n01JAZAYGWIGY16abc","isActive":"1","vendorId":"00901MRFYEUWEUMIm7gt","invoiceNumber":"1","approvalStatus":"0","invoiceDate":"2015-01-19","dueDate":"2015-02-18","glPostingDate":"2015-01-19","amount":66.67,"scheduledAmount":0,"paidAmount":null,"paymentStatus":"1","description":null,"createdTime":"2015-01-19T11:35:57.000+0000","updatedTime":"2015-01-19T11:35:57.000+0000","payFromBankAccountId":"00000000000000000000","payFromChartOfAccountId":"00000000000000000000","billLineItems":[{"entity":"BillLineItem","id":"bli01PKBRTDWZAN19xm6","billId":"00n01JAZAYGWIGY17x3y","amount":66.67,"chartOfAccountId":"00000000000000000000","departmentId":"00000000000000000000","locationId":"00000000000000000000","jobId":"00000000000000000000","customerId":"00000000000000000000","jobBillable":false,"description":null,"createdTime":"2015-01-19T11:35:58.000+0000","updatedTime":"2015-01-19T11:35:58.000+0000","lineType":"1","itemId":"00000000000000000000","quantity":null,"unitPrice":null,"employeeId":"00000000000000000000","actgClassId":"00000000000000000000"}]}]

  // List specific entity with ID
  echo json_encode($api->read('Bill','00n01JAZAYGWIGY17x3y')).PHP_EOL;
  //Response: {"entity":"Bill","id":"00n01JAZAYGWIGY17x3y","isActive":"1","vendorId":"00901MRFYEUWEUMIm7gt","invoiceNumber":"1","approvalStatus":"0","invoiceDate":"2015-01-19","dueDate":"2015-02-18","glPostingDate":"2015-01-19","amount":66.67,"scheduledAmount":0,"paidAmount":null,"paymentStatus":"1","description":null,"createdTime":"2015-01-19T11:35:57.000+0000","updatedTime":"2015-01-19T11:35:57.000+0000","payFromBankAccountId":"00000000000000000000","payFromChartOfAccountId":"00000000000000000000","billLineItems":[{"entity":"BillLineItem","id":"bli01PKBRTDWZAN19xm6","billId":"00n01JAZAYGWIGY17x3y","amount":66.67,"chartOfAccountId":"00000000000000000000","departmentId":"00000000000000000000","locationId":"00000000000000000000","jobId":"00000000000000000000","customerId":"00000000000000000000","jobBillable":false,"description":null,"createdTime":"2015-01-19T11:35:58.000+0000","updatedTime":"2015-01-19T11:35:58.000+0000","lineType":"1","itemId":"00000000000000000000","quantity":null,"unitPrice":null,"employeeId":"00000000000000000000","actgClassId":"00000000000000000000"}]}

  // List specific entity with filter, limit and sort
  echo json_encode($api->get_list('Vendor', array(
      "start" => 0,
      "max" => 999,
      "filters" => array(
          array(
              "field" => "createdTime",
              "op" => "<=",
              "value" => "2018-01-18T12:30:40.841+0000"
          )
      ),
      "sort" => array(
          array(
              "field" => "createdTime",
              "asc" => "false"
          )
      )
  ))).PHP_EOL;
  //Response: [{"entity":"Bill","id":"00n01JAZAYGWIGY17x3y", ...}]

  //Create a Bill
  $today = date("Y-m-d");
  $inTwoDays =  date('Y-m-d', strtotime(date('Y-m-d') .' +2 days'));
  $newBill = array(
      "entity" => "Bill",
      "isActive" => "1",
      "vendorId" => $vendors[0]['id'],
      "invoiceNumber" => "Payout #".time(), // should be unique
      "invoiceDate" => $today,
      "dueDate" => $today,
      "amount" => 250.00,
      "glPostingDate" => $today,
      "billLineItems" => array(
          array(
              "entity" => "BillLineItem",
              "amount" => 200.00,
              "chartOfAccountId" => "0ca01YVKNIJQMFOG5y5k", // Custom, specific expense type, see https://bill.com/ChartOfAccount?ql=1010
              "description" => "Movement #1532 payout",
              "lineType" => "1"
          ),
          array(
              "entity" => "BillLineItem",
              "amount" => 50.00,
              "chartOfAccountId" => "0ca01YVKNIJQMFOG5y5k", // Custom, specific expense type, see https://bill.com/ChartOfAccount?ql=1010
              "description" => "Movement #1533 payout",
              "lineType" => "1"
          )
      )
  );

  $createdBill = $api->create("Bill",$newBill);
  echo json_encode($createdBill).PHP_EOL;
  //Response: {"entity":"Bill","id":"00n01UJMJXXLVUF17xca","isActive":"1","vendorId":"00901MRFYEUWEUMIm7gt","invoiceNumber":"Payout #1421678275","approvalStatus":"0","invoiceDate":"2015-01-19","dueDate":"2015-01-19","glPostingDate":"2015-01-19","amount":250,"scheduledAmount":0,"paidAmount":null,"paymentStatus":"1","description":null,"createdTime":"2015-01-19T14:37:55.000+0000","updatedTime":"2015-01-19T14:37:55.000+0000","payFromBankAccountId":"00000000000000000000","payFromChartOfAccountId":"00000000000000000000","billLineItems":[{"entity":"BillLineItem","id":"bli01VFPKAXMJXI19xui","billId":"00n01UJMJXXLVUF17xca","amount":200,"chartOfAccountId":"0ca01YVKNIJQMFOG5y5k","departmentId":"00000000000000000000","locationId":"00000000000000000000","jobId":"00000000000000000000","customerId":"00000000000000000000","jobBillable":false,"description":"Movement #1532 payout","createdTime":"2015-01-19T14:37:56.000+0000","updatedTime":"2015-01-19T14:37:56.000+0000","lineType":"1","itemId":"00000000000000000000","quantity":null,"unitPrice":null,"employeeId":"00000000000000000000","actgClassId":"00000000000000000000"},{"entity":"BillLineItem","id":"bli01TUJXITZVRX19xuj","billId":"00n01UJMJXXLVUF17xca","amount":50,"chartOfAccountId":"0ca01YVKNIJQMFOG5y5k","departmentId":"00000000000000000000","locationId":"00000000000000000000","jobId":"00000000000000000000","customerId":"00000000000000000000","jobBillable":false,"description":"Movement #1533 payout","createdTime":"2015-01-19T14:37:56.000+0000","updatedTime":"2015-01-19T14:37:56.000+0000","lineType":"1","itemId":"00000000000000000000","quantity":null,"unitPrice":null,"employeeId":"00000000000000000000","actgClassId":"00000000000000000000"}]}

  // now you can pay either with bill.com API(ACH, Paypal, Mastercard RPPS), or mark bill as already paid externally
  // Pay with bill.com
  json_encode($api->pay_bill(array(
              "billId" => $createdBill['id'],
              "amount" => 250.00,
              "processDate" => $inTwoDays // sometimes, tommorrow's date just does not work, give more time.
          )
      )) . PHP_EOL;
  //Response: Entity SentPay, Details unknown, need to setup bank account first :(

  // Mark as external payment
  echo json_encode($api->record_bill_payed(array(
          "vendorId" => $vendors[0]['id'],
          "toPrintCheck" => false,
          "processDate" => $inTwoDays,
          "chartOfAccountId" => "0ca01YVKNIJQMFOG5y5k",
          "description" => "Paypal payment #EC-29176441",
          "billPays" => array(
              array(
                  "entity" => "BillPay",
                  "billId" => $createdBill['id'],
                  "amount" => 250.00
              )
          )
      )
  )) . PHP_EOL;
  // Response: {"entity":"SentPay","id":"stp01TAWIFJUNMKFvzfs","processDate":"2015-01-21","amount":250,"status":"2","description":"Paypal payment #EC-29176441","txnNumber":"LTQCSOZDGREJAAAAAAAA","name":"P15011901 - 2725827","vendorId":"00901MRFYEUWEUMIm7gt","isOnline":false,"paymentType":"4","chartOfAccountId":"0ca01YVKNIJQMFOG5y5k","syncReference":null,"toPrintCheck":false,"createdTime":"2015-01-19T14:37:57.329+0000","updatedTime":"2015-01-19T14:37:57.559+0000","bankAccountId":"00000000000000000000","billPays":[{"entity":"BillPay","id":"blp01CRPFLCKVPB1mf9g","billId":"00n01UJMJXXLVUF17xca","name":"P15011901 - 2725827","paymentStatus":"2","amount":250,"description":"Paypal payment #EC-29176441","processDate":"2015-01-21","createdTime":"2015-01-19T14:37:57.000+0000","updatedTime":"2015-01-19T14:37:57.000+0000","paymentType":"1","syncReference":null,"toPrintCheck":false,"chartOfAccountId":"0ca01YVKNIJQMFOG5y5k"}],"billCredits":[],"voidRequests":[]}


} catch (\BillComApi\BillComException e){
  var_dump(e);
}
```
