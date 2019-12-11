<?php

namespace Swissup\Marketplace\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Swissup\Marketplace\Job\ChannelsSave;
use Swissup\Marketplace\Model\Job as AsyncJob;
use Swissup\Marketplace\Service\JobDispatcher;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_Marketplace::settings_save';

    /**
     * @var JobDispatcher
     */
    protected $dispatcher;

    /**
     * @param Context $context
     * @param JobDispatcher $dispatcher
     */
    public function __construct(
        Context $context,
        JobDispatcher $dispatcher
    ) {
        $this->dispatcher = $dispatcher;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response = new \Magento\Framework\DataObject();
        $channels = $this->getRequest()->getPostValue('channels');

        if ($channels) {
            try {
                $job = $this->dispatcher->dispatch(ChannelsSave::class, [
                    'data' => $channels
                ]);

                if ($job instanceof AsyncJob) {
                    $response->addData([
                        'message' => __('Please wait a minute until the changes will take place.'),
                        'id' => $job->getId(),
                        'created_at' => $job->getCreatedAt(),
                    ]);
                } else {
                    $this->messageManager->addSuccess(__('Settings successfully updated.'));
                    $response->addData(['reload' => true]);
                }
            } catch (\Exception $e) {
                $response->setMessage($e->getMessage());
                $response->setError(1);
            }
        }

        return $resultJson->setData($response);
    }
}
