# bill-dot-com-php-api
PHP Bill.com API from http://developer.bill.com/api-documentation/code-samples/php/ nicely packaged.

Bill.com is software product that allows to manage your company financial flows, organize interaction with suppliers and clients and track your expenses and payables .

This package is heavily influenced by official code samples, but is not limited to code in official sample.

##Installation
This package is "composer"-ed. To use it, just add "altmind/bill-dot-com-php-api": "dev-master" in composer.json deps.

Packagist page: https://packagist.org/packages/altmind/bill-dot-com-php-api

##Usage Examples
```php
$client = new \BillComApi\BillCom("YOUR_DEV_KEY","YOUR_PASS","YOUR_USERNAME"/*, "HOSTNAME" */);
try{
  $client->login()
  var_dump($client->list_orgs());
  var_dump($client->get_list('Bill'));
} catch (\BillComApi\BillComException e){
  var_dump(e);
  die(e);
}
```
