<?php

/**
 * The namespaces provided by the SDK.
 */
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

class store
{   
    public function getStore()
    {
         require __DIR__.'/../vendor/autoload.php';

        //load the configuration's file
        $config = require __DIR__.'/../vendor/configuration.php';
        
        $service = new Services\TradingService(array(
            'apiVersion' => $config['tradingApiVersion'],
            'siteId' => Constants\SiteIds::FR
        ));

        /**
         * Create the request object.
         *
         * For more information about creating a request object, see:
         * http://devbay.net/sdk/guides/getting-started/#request-object
         */
        $request = new Types\GetStoreRequestType();

        /**
         * An user token is required when using the Trading service.
         *
         * NOTE: eBay will use the token to determine which store to return.
         *
         * For more information about getting your user tokens, see:
         * http://devbay.net/sdk/guides/application-keys/
         */
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $config['production']['userToken'];

        /**
         * Send the request to the GetStore service operation.
         *
         * For more information about calling a service operation, see:
         * http://devbay.net/sdk/guides/getting-started/#service-operation
         */
        $response = $service->getStore($request);
        $stores = '';

        /**
         * Output the result of calling the service operation.
         *
         * For more information about working with the service response object, see:
         * http://devbay.net/sdk/guides/getting-started/#response-object
         */
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                printf("%s: %s\n%s\n\n",
                    $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                    $error->ShortMessage,
                    $error->LongMessage
                );
            }
        }

        if ($response->Ack !== 'Failure') {
            $store = $response->Store;
            
            foreach ($store->CustomCategories->CustomCategory as $category) {
                $storess[] = $this->printCategory($category, 1);
            }
            $stores = array(
                'Name' => $store->Name,
                'Description' =>$store->Description,
                'URL' => $store->URL,
                'Data' => $storess );
            return $stores;
        }

        
    }
}



