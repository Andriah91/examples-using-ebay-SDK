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

class Selling
{
    
    public function getSelling()
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
		$request = new Types\GetMyeBaySellingRequestType();

		/**
		 * An user token is required when using the Trading service.
		 *
		 * For more information about getting your user tokens, see:
		 * http://devbay.net/sdk/guides/application-keys/
		 */
		$request->RequesterCredentials = new Types\CustomSecurityHeaderType();
		$request->RequesterCredentials->eBayAuthToken = $config['production']['userToken'];

		/**
		 * Request that eBay returns the list of actively selling items.
		 * We want 10 items per page and they should be sorted in descending order by the current price.
		 */
		$request->ActiveList = new Types\ItemListCustomizationType();
		$request->ActiveList->Include = true;
		$request->ActiveList->Pagination = new Types\PaginationType();
		$request->ActiveList->Pagination->EntriesPerPage = 20;
		$request->ActiveList->Sort = Enums\ItemSortTypeCodeType::C_CURRENT_PRICE_DESCENDING;

		$pageNum = 1;
		$data;
		//do {
		    $request->ActiveList->Pagination->PageNumber = $pageNum;

		    /**
		     * Send the request to the GetMyeBaySelling service operation.
		     *
		     * For more information about calling a service operation, see:
		     * http://devbay.net/sdk/guides/getting-started/#service-operation
		     */
		    $response = $service->getMyeBaySelling($request);

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

		    if ($response->Ack !== 'Failure' && isset($response->ActiveList)) {
		        foreach ($response->ActiveList->ItemArray->Item as $item) {
		        	$data[] = $item->toArray();
		            /*printf("(%s) %s: %s %.2f\n",
		                $item->ItemID,
		                $item->Title,
		                $item->SellingStatus->CurrentPrice->currencyID,
		                $item->SellingStatus->CurrentPrice->value
		            );*/
		        }
		    }

		    $pageNum += 1;

		//} while(isset($response->ActiveList) && $pageNum <= $response->ActiveList->PaginationResult->TotalNumberOfPages);

		return $data;
    }
}