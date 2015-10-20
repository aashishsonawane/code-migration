<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

use Magento\Migration\Code\ModuleMigration\ModuleFileCopier;
use Magento\Migration\Code\ModuleMigration\ModuleFileExtractor;
use Magento\Framework\ObjectManagerInterface;

class LayoutConverter
{
    /**
     * @var string
     */
    protected $m2Path;

    /**
     * @var string
     */
    protected $m1Path;


    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\LayoutConverter\LayoutHalndlerExtractorFactory
     */
    protected $layoutHalndlerExtractorFactory;

    /**
     * @var \Magento\Migration\Code\LayoutConverter\LayoutHalndlerFileFactory
     */
    protected $layoutHalndlerFileFactory;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Migration\Code\LayoutConverter\LayoutHalndlerExtractorFactory $layoutHalndlerExtractorFactory
     * @param \Magento\Migration\Code\LayoutConverter\LayoutHalndlerFileFactory $layoutHalndlerFileFactory
     * @param string $m2Path
     * @param string|null $m1Path
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Migration\Code\LayoutConverter\LayoutHalndlerExtractorFactory $layoutHalndlerExtractorFactory,
        \Magento\Migration\Code\LayoutConverter\LayoutHalndlerFileFactory $layoutHalndlerFileFactory,
        $m2Path,
        $m1Path = null
    ) {
        $this->m2Path = $m2Path;
        $this->m1Path = $m1Path;
        $this->file = $file;
        $this->logger = $logger;
        $this->layoutHalndlerExtractorFactory = $layoutHalndlerExtractorFactory;
        $this->layoutHalndlerFileFactory = $layoutHalndlerFileFactory;
    }

    /**
     * @param string $file
     */
    public function processLayoutHandlers($file)
    {
        $extractor = $this->layoutHalndlerExtractorFactory->create(['layoutHandlerFile' => $file]);
        $cnt = 0;
        $handlers = $extractor->getLayoutHandlers();
        if (is_array($handlers)) {
            foreach ($handlers as $handlerFileName => $xml) {
                $fileHandler = $this->layoutHalndlerFileFactory->create(
                    [
                        'handlerFileName' => $handlerFileName,
                        'xml' => $xml,
                        'm1Path' => $this->m1Path,
                    ]
                );
                if ($fileHandler->createFileHandler()) {
                    $this->logger->info('Created M2 layout file ' . $handlerFileName);
                } else {
                    $this->logger->warn('Error creating M2 layout file ' . $handlerFileName);
                }
                $cnt++;
            }
            if ($cnt) {
                $this->deleteM1LayoutFile($file);
            } else {
                $this->logger->warn('Ignoring M1 layout file due to lack of contents ' . $file);
            }
        }
    }

    protected function deleteM1LayoutFile($file)
    {
        if ($this->file->deleteFile($file)) {
            $this->logger->info('Deleted M1 layout file' . $file);
        } else {
            $this->logger->warn('Error deleting M1 layout file ' . $file);
        }
    }
}
