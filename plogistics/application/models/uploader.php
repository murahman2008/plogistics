<?php

class Uploader extends CI_Model
{
	private $_ebayColumnArray = array(
			'saleRecordNumber',
			'ebayUserId',
			'buyerFullName',
			'buyerPhone',
			'buyerEmail',
			'buyerAddress1',
			'buyerAddress2',
			'buyerCity',
			'buyerState',
			'buyerPostcode',
			'buyerCountry',
			'itemNo',
			'itemTitle',
			'customLabel',
			'quantity',
			'salePrice',
			'postageAndHandling',
			'insurance',
			'cashOnDeliveryFee',
			'totalPrice',
			'paymentMethod',
			'saleDate',
			'checkoutDate',
			'paidOnDate',
			'postedOnDate',
			'feedbackLeft',
			'feedbackReceived',
			'notesToYourself',
			'paypalTransactionId',
			'postageService',
			'cashOnDeliveryOption',
			'transactionId',
			'orderId',
			'variationDetails',
			'globalShippingProgram',
			'globalShippingReferenceId',
			'clickAndCollect',
			'clickAndCollectReferenceNo',
			'postToAddress1',
			'postToAddress2',
			'postToCity',
			'postToState',
			'postToPostalCode',
			'postToCountry',
			'ebayPlus'
	);
	
	private $_mandatoryEbayColumnArray = array(
			'saleRecordNumber',
			'ebayUserId',
			'buyerFullName',
			'buyerEmail',
			'itemNo',
			'itemTitle',
			//'customLabel',
			'quantity',
			'salePrice',
			'totalPrice',
			'checkoutDate',
			'paidOnDate',
			'transactionId',
			//'orderId',
			'postageService',
			'paymentMethod'
	);
	
	private $_ebayPostageServiceArray = array('expedited shipping', 'freight');
	
	public function __construct()
	{
		parent::__construct();
	}
	
	private function _covertEbayPrice($price)
	{
		$price = trim(str_replace('$', '', str_ireplace('au', '', $price)));
		return $price;
	}
	
	public function uploadFile(Array $fileArray, $fileName = '')
	{
		if(($fileName = trim($fileName)) === '')
			$fileName = date('YmdHis').'_'.$fileArray['name'];
		
		$fileNameArray = explode(".", $fileArray['name']);
		$ext = strtolower(trim($fileNameArray[count($fileNameArray) - 1]));
		
		$fileNameArray2 = explode(".", $fileName);
		$ext2 = strtolower(trim($fileNameArray2[count($fileNameArray2) - 1]));
		
		if($ext !== $ext2)
			throw new Exception('Extension mismatch between uploaded file ['.$fileArray['name'].'] and saving file ['.$fileName.']');
		
		$finalFilePath = FCPATH.'assets/tmp/'.$fileName;
		
		if(file_exists($finalFilePath))
			@unlink($finalFilePath);
		
		if(!move_uploaded_file($fileArray['tmp_name'], $finalFilePath))
			throw new Exception('Unable to upload file ['.$fileArray['name'].']');
		
		return $finalFilePath;
	}
	
	public function uploadEbaySellOrderFile(Array $fileArray)
	{
		$this->load->model('sell_order');
		$fileError = $fileSuccess = array();
		
		$filePath = $this->uploadFile($fileArray);
		
		if(count($this->_ebayColumnArray) !== EBAY_COLUMN_COUNT)
			throw new Exception("System Error: Ebay csv column count is set as [".EBAY_COLUMN_COUNT."] but the setup has column count of [".count($this->_ebayColumnArray)."]");
		
		$fp = fopen($filePath, "r");
		if(!$fp)
			throw new Exception("Unable to read file [".$filePath."]");
		
		$lineCounter = 0;
		
		while(($data = fgetcsv($fp)) !== false)
		{
			if(count($data) <= 0 || count($data) !== EBAY_COLUMN_COUNT)
				continue;
				
			$lineCounter++;
			if($lineCounter === 1)
				continue;
				
			try
			{
				$error = array();
				for($i = 0; $i < count($this->_ebayColumnArray); $i++)
				{
					$var = $this->_ebayColumnArray[$i];
					$$var=  '';
					$$var = trim($data[$i]);
					
					if(in_array($var, $this->_mandatoryEbayColumnArray) && $$var === '')
						$error[] = "Missing Value for Column [".$var."]";
				}
							
				if(count($error) > 0)
					throw new Exception(implode("<br/>", $error));

				$orderTotalInc = $this->_covertEbayPrice($totalPrice);

				if(!is_numeric($orderTotalInc) || $orderTotalInc <= 0)
					throw new Exception('The Sale Price ['.$totalPrice.'] is invalid');

				$postageCostInc = $this->_covertEbayPrice($postageAndHandling);
					
				$customer = false;
				$state = false;
				$sellSourceId = SELL_SOURCE_EBAY;
				$sellOrderStatusId = SELL_ORDER_STATUS_DISPATCHED;
				$paymentReceivedDate = $paymentMethodId = $paymentReferenceNo = false;
				$sellOrderAddressLine1 = $sellOrderAddressLine2 = $sellOrderPostcode = $sellOrderSuburb = $firstName = $lastName = '';
				$sellOrderState = false;
				$sellOrderPhone = $sellOrderEmail = '';
				$deliveryMethodId = DELIVERY_METHOD_DELIVERY;
				$sellOrderId = false;

				list($firstName, $lastName) = explode(" ", $buyerFullName);
														
				if($buyerState !== '')
				{
					$stateArray = $this->customer->getStates(array('name like' => $buyerState));
					if(count($stateArray) > 0)
						$state = $stateArray[0];
				}
				$state = ($state !== false ? $state->id : 0);
		
				/// first check if the sell_order already exists in the system ///
				$existingSellOrder = false;
				$insert = true;

				$esoArray = $this->sell_order->getSellOrders(array('external_identifier' => $saleRecordNumber));
				if(count($esoArray) > 0)
				{
					$existingSellOrder = $esoArray[0];
					$insert = false;
					$sellOrderId = $existingSellOrder->id;
				}
				//////////////////////////////////////////////////////////////
		
				if(strtolower($paymentMethod) === 'paypal')
				{
					$paymentMethodId = PAYMENT_METHOD_PAYPAL;
					$paymentReferenceNo = $paypalTransactionId;
				}

				if(validateDateTime($paidOnDate, 'd-M-y'))
					$paymentReceivedDate = convertDateTime($paidOnDate);
		
				$additionalComments = array();
				$additionalComments[] = "Ebay Sell Record No: [".$saleRecordNumber."]";
				$additionalComments[] = "Ebay Transaction Id: [".$transactionId."]";
				$additionalComments[] = "Ebay Item No: [".$itemNo."]";
				$additionalComments[] = "Ebay Item Title: [".$itemTitle."]";
				$additionalComments[] = "Ebay Note: [".$notesToYourself."]";
				$additionalComments[] = "Ebay Sale Date: [".$saleDate."]";
				$additionalComments[] = "Ebay Checkout Date: [".$checkoutDate."]";
				$additionalComments[] = "Ebay Click and Collect: [".$clickAndCollect."]";
				$additionalComments[] = "Ebay Click and Collect Ref No: [".$clickAndCollectReferenceNo."]";
				$additionalComments = implode("<*>", $additionalComments);

				/// figure out the address for the sell order (if required) ///
				$sellOrderAddressLine1 = ($postToAddress1 !== '' ? $postToAddress1 : $buyerAddress1);
				$sellOrderAddressLine2 = ($postToAddress2 !== '' ? $postToAddress2 : $buyerAddress2);
				$sellOrderSuburb = ($postToCity !== '' ? $postToCity : $buyerCity);
				$sellOrderPostcode = ($postToPostalCode !== '' ? $postToPostalCode : $buyerPostcode);
														
				if($postToState == $buyerState)
					$sellOrderState = $state;
				else
				{
					$psArray = $this->customer->getStates(array('name like' => $postToState));
					if(count($psArray) > 0)
						$sellOrderState = $psArray[0];
						
					$sellOrderState = ($sellOrderState !== false ? $sellOrderState->id : 0);
				}

				if($deliveryMethodId == DELIVERY_METHOD_DELIVERY)
				{
					if(($sellOrderAddressLine1 == '' && $sellOrderAddressLine2 == '') || $sellOrderPostcode == '' ||
						$sellOrderSuburb == '' || ($sellOrderState == '' || $sellOrderState === false || $sellOrderState == 0))
					{
						throw new Exception("Postal Address is required for this order as the delivery method is marked as [".$postageService."]. Address information missing");
					}
				}
				///////////////////////////////////////////////////////////////////////////////////////////
		
				$sellOrderContactNo = $buyerPhone;
				$sellOrderEmail = $buyerEmail;

				$sellOrderCriteria = $customerCriteria = array();
				$customerCriteria = array(
						'first_name' => $firstName,
						'last_name' => $lastName,
						'phone' => $buyerPhone,
						'email' => $buyerEmail,
						'ebay_id' => $ebayUserId,
						'address_line_1' => $buyerAddress1,
						'address_line_2' => $buyerAddress2,
						'address_suburb' => $buyerCity,
						'address_state_id' => $state,
						'address_postcode' => $buyerPostcode
				);
														
				$sellOrderCriteria = array('sell_order_status_id' => $sellOrderStatusId,
						'sell_source_id' => $sellSourceId,
						'external_identifier' => $saleRecordNumber,
						'order_total_ex' => '',
						'order_total_inc' => $orderTotalInc,
						'payment_method_id' => $paymentMethodId,
						'payment_reference_no' => $paymentReferenceNo,
						'additional_comments' => $additionalComments,
						'payment_received_date' => $paymentReceivedDate,
						'delivery_method_id' => $deliveryMethodId,
						'postage_cost_inc' => $postageCostInc,
						'address_line_1' => $sellOrderAddressLine1,
						'address_line_2' => $sellOrderAddressLine2,
						'suburb' => $sellOrderSuburb,
						'state_id' => $sellOrderState,
						'postcode' => $sellOrderPostcode,
						'product_instances' => array(),
						'email' => $sellOrderEmail,
						'phone' => $sellOrderPhone
				);

				if($insert == true)
					$sellOrderId = $this->sell_order->createSellOrder($customerCriteria, $sellOrderCriteria);
				else
					$sellOrderId = $this->sell_order->updateSellOrder($sellOrderId, $updateCriteria);
		
				if($sellOrderId === false)
					throw new Exception('Cannot Create/Update Sell Order. Unknow Reason....');
			}
			catch(Exception $ex)
			{
				$fileError[] = "Ebay Sell List CSV [".$filePath."], Line [".$lineCounter."] has the following problems:<br/>".$ex->getMessage();
			}
		
			echo "<hr/>";
		}
		fclose($fp);
		
		@unlink($filePath);
		
		$lineCounter = ($lineCounter - 1);
		
		$output['message'] = "Total Line processed [".$lineCounter."]";
		$output['error'] = $fileError;

		return $output;
	}
}