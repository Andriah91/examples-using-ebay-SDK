<?php
/**
 * Created by PhpStorm.
 * User: Rasoul
 * Date: 18/11/2015
 * Time: 16:29
 */

//The namespaces provided by the SDK
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

class Orders
{
    
    public function getOrders($date,$dat)
    {
        

        require __DIR__.'/../vendor/autoload.php';

        //load the configuration's file
        $config = require __DIR__.'/../vendor/configuration.php';

        $service = new Services\TradingService(array(
            'apiVersion' => $config['tradingApiVersion'],
            'siteId' => Constants\SiteIds::FR,
            'sandbox'=> false,
        ));

        //$service = $this->getTradingService();

        $args = array(
            //"OrderStatus"   => "Completed",
            "OrderStatus"   => "All",
            "SortingOrder"  => "Ascending",
            "OrderRole"     => "Seller",

            //"CreateTimeFrom"   => new \DateTime('2015-01-01'),
            
            "CreateTimeFrom"   => new \DateTime((string)$date),
            "CreateTimeTo"   => new \DateTime((string)$dat)
        );

        $request = new Types\GetOrdersRequestType($args);
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $config['production']['userToken'];
        $request->IncludeFinalValueFee = true;
        $request->Pagination = new Types\PaginationType();
        $request->Pagination->EntriesPerPage = 100;
        $pageNum = 1;

        $orders;

        do {
            $request->Pagination->PageNumber = $pageNum;

            $response = $service->getOrders($request);

            if (isset($response->Errors)) {

                $message = '';

                foreach ($response->Errors as $error) {
                    $message .= $error->ShortMessage;
                }

                throw new Exception($message);
            }

            if ($response->Ack !== 'Failure' && isset($response->OrderArray)) {
                foreach ($response->OrderArray->Order as $order) {
                    $orders[] = $order->toArray();
                }
            }

            $pageNum += 1;
        }
        while(isset($response->OrderArray) && $pageNum <= $response->PaginationResult->TotalNumberOfPages);

            return $orders;
        
    }

    public function getDate()
    {
        return $this->db->query('SELECT `date` FROM commande ORDER BY id DESC limit 1')->result();
    }
}