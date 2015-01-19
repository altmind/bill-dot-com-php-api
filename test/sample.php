<?php
date_default_timezone_set('UTC');
require_once "autoload.php";


// see APIDoc http://developer.bill.com/api-documentation/api/chart-accounts/
$api = new \BillComApi\BillCom("AAAAAAAAAAAAAABBBBBBBCCCCC","PASSPASSPASS","LOGINLOGIN");

//echo json_encode($api->login()).PHP_EOL; // optional, will be performed internally automatically, on first request that require auth
//RS: {}

//echo json_encode($api->list_orgs()).PHP_EOL; // optional, will be performed internally automatically, on first request that require org_id
//RS: [{"orgId":"00123ABCDEFGHIGK4abc","orgName":"ThreadMeUp"}]


/*
Listable:
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
//echo json_encode($api->get_list('Bill')).PHP_EOL;
//RS: [{"entity":"Bill","id":"00n01JAZAYGWIGY17x3y","isActive":"1","vendorId":"00901MRFYEUWEUMIm7gt","invoiceNumber":"1","approvalStatus":"0","invoiceDate":"2015-01-19","dueDate":"2015-02-18","glPostingDate":"2015-01-19","amount":66.67,"scheduledAmount":0,"paidAmount":null,"paymentStatus":"1","description":null,"createdTime":"2015-01-19T11:35:57.000+0000","updatedTime":"2015-01-19T11:35:57.000+0000","payFromBankAccountId":"00000000000000000000","payFromChartOfAccountId":"00000000000000000000","billLineItems":[{"entity":"BillLineItem","id":"bli01PKBRTDWZAN19xm6","billId":"00n01JAZAYGWIGY17x3y","amount":66.67,"chartOfAccountId":"00000000000000000000","departmentId":"00000000000000000000","locationId":"00000000000000000000","jobId":"00000000000000000000","customerId":"00000000000000000000","jobBillable":false,"description":null,"createdTime":"2015-01-19T11:35:58.000+0000","updatedTime":"2015-01-19T11:35:58.000+0000","lineType":"1","itemId":"00000000000000000000","quantity":null,"unitPrice":null,"employeeId":"00000000000000000000","actgClassId":"00000000000000000000"}]}]

//echo json_encode($api->read('Bill','00n01JAZAYGWIGY17x3y')).PHP_EOL;
//RS: {"entity":"Bill","id":"00n01JAZAYGWIGY17x3y","isActive":"1","vendorId":"00901MRFYEUWEUMIm7gt","invoiceNumber":"1","approvalStatus":"0","invoiceDate":"2015-01-19","dueDate":"2015-02-18","glPostingDate":"2015-01-19","amount":66.67,"scheduledAmount":0,"paidAmount":null,"paymentStatus":"1","description":null,"createdTime":"2015-01-19T11:35:57.000+0000","updatedTime":"2015-01-19T11:35:57.000+0000","payFromBankAccountId":"00000000000000000000","payFromChartOfAccountId":"00000000000000000000","billLineItems":[{"entity":"BillLineItem","id":"bli01PKBRTDWZAN19xm6","billId":"00n01JAZAYGWIGY17x3y","amount":66.67,"chartOfAccountId":"00000000000000000000","departmentId":"00000000000000000000","locationId":"00000000000000000000","jobId":"00000000000000000000","customerId":"00000000000000000000","jobBillable":false,"description":null,"createdTime":"2015-01-19T11:35:58.000+0000","updatedTime":"2015-01-19T11:35:58.000+0000","lineType":"1","itemId":"00000000000000000000","quantity":null,"unitPrice":null,"employeeId":"00000000000000000000","actgClassId":"00000000000000000000"}]}

//echo json_encode($api->get_list('Vendor')).PHP_EOL;
//RS: [{"entity":"Vendor","id":"00901MRFYEUWEUMIm7gt","isActive":"1","name":"TestVendor","shortName":null,"nameOnCheck":null,"companyName":null,"accNumber":null,"taxId":"","track1099":false,"address1":null,"address2":null,"address3":null,"address4":null,"addressCity":null,"addressState":null,"addressZip":null,"addressCountry":"United States","email":"testvendor@gmail.com","fax":null,"phone":null,"payBy":"0","paymentEmail":"testvendor@gmail.com","paymentPhone":null,"description":null,"createdTime":"2015-01-19T11:25:18.000+0000","updatedTime":"2015-01-19T11:36:03.000+0000","contactFirstName":null,"contactLastName":null,"mergedIntoId":"00000000000000000000","accountType":"0"}]

$vendors = $api->get_list('Vendor', array(
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
));

echo json_encode($vendors).PHP_EOL;

$today = date("Y-m-d");
$inTwoDays =  date('Y-m-d', strtotime(date('Y-m-d') .' +2 days'));
$newBill = array(
    "entity" => "Bill",
    "isActive" => "1",
    "vendorId" => $vendors[0]['id'],
    "invoiceNumber" => "Payout #".time(),
    "invoiceDate" => $today,
    "dueDate" => $today,
    "amount" => 250.00,
    "glPostingDate" => $today,
    "billLineItems" => array(
        array(
            "entity" => "BillLineItem",
            "amount" => 200.00,
            "chartOfAccountId" => "0ca01YVKNIJQMFOG5y5k", // see https://app-stage.bill.com/ChartOfAccount?ql=1010
            "description" => "Movement #1532 payout",
            "lineType" => "1",
        ),
        array(
            "entity" => "BillLineItem",
            "amount" => 50.00,
            "chartOfAccountId" => "0ca01YVKNIJQMFOG5y5k", // see https://app-stage.bill.com/ChartOfAccount?ql=1010
            "description" => "Movement #1533 payout",
            "lineType" => "1"
        )
    )
);
$createdBill = $api->create("Bill",$newBill);
echo json_encode($createdBill).PHP_EOL;

/*echo json_encode($api->pay_bill(array(
            "billId" => $createdBill['id'],
            "amount" => 260.00,
            "processDate" => $inTwoDays
        )
    )) . PHP_EOL;*/

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
