<?php
/**
 * American Retail Supply
 *
 * reference: https://github.com/weprovide/magento2-module-mailattachment
 */
 
namespace Solwin\Contactwidget\Mail\Template;
 
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @param Api\AttachmentInterface $attachment
     */
    public function addAttachment($fileString)
    {
		\Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug(json_encode($fileString);
        $this->message->createAttachment(
            $fileString,
            'image/jpg',
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            'attatched.jpg'
        );
        return $this;
    }
}