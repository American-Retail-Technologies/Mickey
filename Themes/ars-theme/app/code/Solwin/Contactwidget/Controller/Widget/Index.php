<?php
/**
 * Solwin Infotech
 * Solwin Contact Form Widget Extension
 *
 * @category   Solwin
 * @package    Solwin_Contactwidget
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\Contactwidget\Controller\Widget;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Area;

class Index extends \Magento\Framework\App\Action\Action
{
    const CUSTOM_BAG_EMAIL_TEMPLATE_ID = 'contactwidget_section/emailsend/custom_bag_email_id';
    const CUSTOM_LABEL_EMAIL_TEMPLATE_ID = 'contactwidget_section/emailsend/custom_label_email_id';
    const CUSTOM_TISSUE_EMAIL_TEMPLATE_ID = 'contactwidget_section/emailsend/custom_tissue_email_id';
    const CUSTOM_BOX_EMAIL_TEMPLATE_ID = 'contactwidget_section/emailsend/custom_box_email_id';
    const EMAIL_TEMPLATE = 'contactwidget_section/emailsend/emailtemplate';
    const EMAIL_SENDER = 'contactwidget_section/emailsend/emailsenderto';
    const XML_PATH_EMAIL_RECIPIENT = 'contactwidget_section/emailsend/emailto';
    const REQUEST_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const REQUEST_RESPONSE = 'g-recaptcha-response';

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;
    
    /**
     * @var StateInterface
     */
    protected $_inlineTranslation;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Solwin\Contactwidget\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Solwin\Contactwidget\Helper\Data $helper,
		\Magento\Framework\Filesystem $fileSystem
    ) {
		$this->_filesystem = $fileSystem;
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
    }

    public function execute() {

        $remoteAddr = filter_input(
                INPUT_SERVER,
                'REMOTE_ADDR',
                FILTER_SANITIZE_STRING
                );
        $data = $this->getRequest()->getParams();
		
		//Find form data in debug.log
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectUrl = $data['currUrl'];
		$currUrlPath = parse_url($data['currUrl'], PHP_URL_PATH);
        $secretkey = $this->_helper
                ->getConfigValue(
                        'contactwidget_section/recaptcha/recaptcha_secretkey'
                        );
        $captchaErrorMsg = $this->_helper
                ->getConfigValue(
                        'contactwidget_section/recaptcha/recaptcha_errormessage'
                        );
        
        if ($data['enablerecaptcha']) {
            if ($captchaErrorMsg == '') {
                $captchaErrorMsg = 'Invalid captcha. Please try again.';
            }
            $captcha = '';
            if (filter_input(INPUT_POST, 'g-recaptcha-response') !== null) {
                $captcha = filter_input(INPUT_POST, 'g-recaptcha-response');
            }

            if (!$captcha) {
                $this->messageManager->addError($captchaErrorMsg);
                return $resultRedirect->setUrl($redirectUrl);
            } else {
                $response = file_get_contents(
                        "https://www.google.com/recaptcha/api/siteverify"
                        . "?secret=" . $secretkey
                        . "&response=" . $captcha
                        . "&remoteip=" . $remoteAddr);
                $response = json_decode($response, true);

                if ($response["success"] === false) {
                    $this->messageManager->addError($captchaErrorMsg);
                    return $resultRedirect->setUrl($redirectUrl);
                }
            }
        }

        try {
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if( $currUrlPath !== "/free-catalog-request/" ) {
				if( is_uploaded_file( $_FILES['file-upload']['tmp_name'] ) ){
					if (
						!isset($_FILES['file-upload']['error']) ||
						is_array($_FILES['file-upload']['error'])
					) {
						throw new \RuntimeException('Invalid parameters.');
					}
					
						// Check $_FILES['file-upload']['error'] value.
					switch ($_FILES['file-upload']['error']) {
						case UPLOAD_ERR_OK:
							break;
						case UPLOAD_ERR_NO_FILE:
							throw new \RuntimeException('No file sent.');
						case UPLOAD_ERR_INI_SIZE:
						case UPLOAD_ERR_FORM_SIZE:
							throw new \RuntimeException('Exceeded filesize limit. Must be less than 1MB.');
						default:
							throw new \RuntimeException('Unknown errors.');
					}

					// You should also check filesize here. 
					if ($_FILES['file-upload']['size'] > 1048576) {
						throw new \RuntimeException('Exceeded filesize limit. Must be less than 1MB.');
					}
					$acceptedFileType = array('img', 'jpg', 'jpeg', 'ai', 'eps', 'pdf', 'png');
					$uploadedFileType = strtolower(pathinfo($_FILES['file-upload']['name'], PATHINFO_EXTENSION));
					if ( !in_array( $uploadedFileType, $acceptedFileType ) ) {
						throw new \RuntimeException("Unacceptable file type...");
					}
					
					//https://www.metagento.com/blog/upload-image-in-magento-2-programmatically.html
					$uploader = $this->_objectManager->create(
						'Magento\MediaStorage\Model\File\Uploader',
						['fileId' => 'file-upload']
					);
					//TODO: add all allowable extensions
					$uploader->setAllowedExtensions(['img', 'jpg', 'jpeg', 'ai', 'eps', 'pdf', 'png']);
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(true);
					
					$mediaDirectory = $this->_filesystem->getDirectoryRead('media');
					$uploadedFileName = md5($data['email'] . time()) . "." . $uploadedFileType;
					$result = $uploader->save($mediaDirectory->getAbsolutePath() . 'custom_product', $uploadedFileName);
					$media_path = $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
					$data['image-path'] = $media_path . 'custom_product' . $result['file'];
					//Path to where the image is uploaded.
					//\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode($data['image-path']));
				}
			}

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($data);
            
			$errorMessage = '';
            $error = false;

            if (!\Zend_Validate::is(trim($data['name']), 'NotEmpty')) {
                $errorMessage .= 'Name is empty, ';
				$error = true;
            }

            if (!\Zend_Validate::is(trim($data['email']), 'NotEmpty')) {
                $errorMessage .= 'Email is empty, ';
				$error = true;
            }
 
			if ( isset( $data['zipcode'] ) && !\Zend_Validate::is(trim($data['zipcode']), 'NotEmpty')) {
                $errorMessage .= 'Zipcode is empty, ';
				$error = true;
            }
 
			if ( !isset($data['cc_title']) && !\Zend_Validate::is(trim($data['comment']), 'NotEmpty')) {
                $errorMessage .= 'Comment is empty , ';
				$error = true;
            }

            if ( !isset($data['cc_title']) && ( isset( $data['address'] ) && !\Zend_Validate::is(trim($data['address']), 'NotEmpty')) ) {
				$errorMessage .= 'Address is empty , ';
                $error = true;
            }

			if ( !isset($data['cc_title']) && ( isset( $data['address2'] ) && !\Zend_Validate::is(trim($data['address2']), 'NotEmpty')) ) {
				$errorMessage .= 'Address2 is empty , ';
				$error = true;
            }

            if ($error) {
                throw new \Exception($errorMessage);
            }

			//Change Email Template based on form template used
            // send mail to recipients
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			if ( $currUrlPath !== "/free-catalog-request/" ){
				//Use different email template based on which page the user came from
				if( $currUrlPath === "/custom-shopping-bags/" || $currUrlPath === "/custom-shopping-bags" ) {
					$templateIdPath = self::CUSTOM_BAG_EMAIL_TEMPLATE_ID;
				} elseif ( $currUrlPath === "/custom-labels-and-stickers/" || $currUrlPath === "/custom-labels-and-stickers" ) {
					$templateIdPath = self::CUSTOM_LABEL_EMAIL_TEMPLATE_ID;
				} elseif ( $currUrlPath === "/custom-tissue-paper-and-gift-wraps/" || $currUrlPath === "/custom-tissue-paper-and-gift-wraps" ) {
					$templateIdPath = self::CUSTOM_TISSUE_EMAIL_TEMPLATE_ID;
				} elseif ( $currUrlPath === "/custom-boxes/" || $currUrlPath === "/custom-boxes" ) {
					$templateIdPath = self::CUSTOM_BOX_EMAIL_TEMPLATE_ID;
				}
				
				$this->_inlineTranslation->suspend();
				$transport = $this->_transportBuilder->setTemplateIdentifier(
							$this->_scopeConfig->getValue(
									$templateIdPath,
									$storeScope
									))
					->setTemplateOptions(
							[
								'area' => Area::AREA_FRONTEND,
								'store' => $this->_storeManager
										->getStore()
										->getId(),
							]
					)->setTemplateVars(['data' => $postObject])
					->setFrom($this->_scopeConfig->getValue(
							self::EMAIL_SENDER, $storeScope
							))
					->addTo( $data['email'] )
					->addBcc( $this->_scopeConfig->getValue(
							self::XML_PATH_EMAIL_RECIPIENT, $storeScope
							) )
					->getTransport();
			}else {
				$this->_inlineTranslation->suspend();
				$transport = $this->_transportBuilder->setTemplateIdentifier(
							$this->_scopeConfig->getValue(
									self::EMAIL_TEMPLATE,
									$storeScope
									)
					)->setTemplateOptions(
							[
								'area' => Area::AREA_FRONTEND,
								'store' => $this->_storeManager
										->getStore()
										->getId(),
							]
					)->setTemplateVars(['data' => $postObject])
					->setFrom($this->_scopeConfig->getValue(
							self::EMAIL_SENDER, $storeScope
							))
					->addTo($this->_scopeConfig->getValue(
							self::XML_PATH_EMAIL_RECIPIENT, $storeScope
							))
					->getTransport();
			}

            $transport->sendMessage();
            $this->_inlineTranslation->resume();
            $this->messageManager->addSuccess(__('Request has been '
                    . 'received. We\'ll respond to you very soon.'));
			if ( $currUrlPath !== "/free-catalog-request/" ){
				//Url with Query String is from Product Listing
				//Otherwise, the user came to the page directly
				$pathToPage = empty(parse_url($redirectUrl,PHP_URL_QUERY)) ? "?success=1" : "&success=1";
				return $resultRedirect->setUrl($redirectUrl . $pathToPage);
			}else {
				return $resultRedirect->setUrl($redirectUrl);
			}
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_inlineTranslation->resume();
            $this->messageManager->addException($e, __('Something went wrong '
                    . 'while sending the contact us request.'));
        }
        return $resultRedirect->setUrl($redirectUrl);
    }

}