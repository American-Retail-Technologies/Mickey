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
		\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode($data));
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectUrl = $data['currUrl'];
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
			if(isset($_FILES['file-upload'])){
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
						throw new \RuntimeException('Exceeded filesize limit.');
					default:
						throw new \RuntimeException('Unknown errors.');
				}

				// You should also check filesize here. 
				if ($_FILES['file-upload']['size'] > 1048576) {
					throw new \RuntimeException('Exceeded filesize limit.');
				}
				//https://www.metagento.com/blog/upload-image-in-magento-2-programmatically.html
				$uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'file-upload']
                );
				//TODO: add all allowable extensions
				$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
				$uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
				
				$mediaDirectory = $this->_filesystem->getDirectoryRead('media');
				$result = $uploader->save($mediaDirectory->getAbsolutePath());
				$media_path = $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
				$data['image-path'] = substr($media_path,0,strlen($media_path)-1) . $result['file'];
				\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode($result));
				\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode($this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA )));
				\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode($data['image-path']));
				\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode(realpath($data['image-path'])));
			}
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($data);
            
            $error = false;

            if (!\Zend_Validate::is(trim($data['name']), 'NotEmpty')) {
                $error = true;
            }

            if (!\Zend_Validate::is(trim($data['email']), 'NotEmpty')) {
                $error = true;
            }
            
			if (!\Zend_Validate::is(trim($data['comment']), 'NotEmpty')) {
                $error = true;
            }
			
            if ( isset( $data['address'] ) && !\Zend_Validate::is(trim($data['address']), 'NotEmpty') ) {
				\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode("address not set & empty"));
                $error = true;
            }
			
			if (isset( $data['address2'] ) && !\Zend_Validate::is(trim($data['address2']), 'NotEmpty') ) {
                \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode("address2 not set & empty"));
				$error = true;
            }
			
            if ($error) {
                throw new \Exception();
            }
			
			//Change Email Template based on form template used
            // send mail to recipients
            $this->_inlineTranslation->suspend();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

			if ( $data['cc_title'] === "Request Custom Packaging" ){
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
				//TODO: Update the email template identifier used for production
				$emailCustomerCopy = $this->_transportBuilder->setTemplateIdentifier('12')
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
					->getTransport();
				$emailCustomerCopy->sendMessage();
			}else {
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
			\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode($transport));
            $transport->sendMessage();
            $this->_inlineTranslation->resume();

            $this->messageManager->addSuccess(__('Request has been '
                    . 'received. We\'ll respond to you very soon.'));
            return $resultRedirect->setUrl($redirectUrl);
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