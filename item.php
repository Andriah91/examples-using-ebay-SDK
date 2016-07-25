<?php

//The namespaces provided by the SDK
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

class items
{
    public function addItem($title,$description,$price,$stock,$conditionID,$items,$store,$categoryID,$ean)
    {
        //autoload file
        require __DIR__.'/../vendor/autoload.php';

        //load the configuration's file
        $config = require __DIR__.'/../vendor/configuration.php';

        //the siteId
        $siteId = Constants\SiteIds::FR;

        $service = new Services\TradingService(array(
            'apiVersion' => $config['tradingApiVersion'],
            'sandbox' => false,
            'siteId' => $siteId
        ));

        $request = new Types\AddFixedPriceItemRequestType();

        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $config['production']['userToken'];


        $item = new Types\ItemType();

  
        $item->Title = $title;
        $item->Description = $description;

        $item->PrimaryCategory = new Types\CategoryType();
        $item->PrimaryCategory->CategoryID = $categoryID;

        $item->ItemSpecifics = new Types\NameValueListArrayType();      
        
        # code...
        if (count($items) != 0)
        {
            for ($i = 0 ; $i < count($items) ; $i++)
            {
                if ($items[$i]->Value != '')
                {
                    $specific = new Types\NameValueListType();
                    $specific->Name = $items[$i]->Name;
                    $specific->Value[] = $items[$i]->Value;
                    $item->ItemSpecifics->NameValueList[] = $specific;
                }
            }
        }
        
        
        $item->ProductListingDetails->EAN = $ean;

        //The store yooshop

        $item->Storefront = new Types\StorefrontType();
        $item->Storefront->StoreCategoryID = $store;

        /**
         * Provide enough information so that the item is listed.
         * It is beyond the scope of this example to go into any detail.
         */

        /**
         * Display a picture with the item.
         */

        $item->PictureDetails = new Types\PictureDetailsType();
        $item->PictureDetails->GalleryType = Enums\GalleryTypeCodeType::C_GALLERY;
        $item->PictureDetails->PictureURL = array('http://ocphonegeeks.com/wp-content/uploads/2014/12/iphone-4g-battery-2.jpg');
        
        $item->ListingType = Enums\ListingTypeCodeType::C_FIXED_PRICE_ITEM;
        $item->Quantity = $stock;
        $item->ListingDuration = Enums\ListingDurationCodeType::C_GTC;
        $item->StartPrice = new Types\AmountType(array('value' => $price));
        $item->Country = 'FR';
        $item->Location = 'Paris';
        $item->Currency = 'EUR';
        $item->ConditionID = $conditionID;
        $item->PaymentMethods[] = 'PayPal';
        $item->PayPalEmailAddress = 'rmacx@wanadoo.fr';
        $item->DispatchTimeMax = 1;
        $item->ShipToLocations[] = 'None';


        $item->ReturnPolicy = new Types\ReturnPolicyType();
        $item->ReturnPolicy->ReturnsAcceptedOption = 'ReturnsAccepted';
        //$item->ReturnPolicy->RefundOption = 'PayPal';
        $item->ReturnPolicy->ReturnsWithinOption = 'Days_14';
        $item->ReturnPolicy->ShippingCostPaidByOption = 'Buyer';

        /**
         * Finish the request object.
         */
        $request->Item = $item;

        /**
         * Send the request to the AddFixedPriceItem service operation.
         *
         * For more information about calling a service operation, see:
         * http://devbay.net/sdk/guides/getting-started/#service-operation
         */
        $response = $service->addFixedPriceItem($request);
        //$response = $service->addItem($request);

        /**
         * Output the result of calling the service operation.
         *
         * For more information about working with the service response object, see:
         * http://devbay.net/sdk/guides/getting-started/#response-object
         */
        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {

                return $error->LongMessage;
            }
        }

        if ($response->Ack !== 'Failure') {
            $data = array ('id_ebay' => $response->ItemID);
            $this->db->where('description' , $description);
            $this->db->update('produits',$data);
            
            return $response->ItemID;
        }

    }
}

