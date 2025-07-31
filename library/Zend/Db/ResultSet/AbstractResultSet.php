<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\ResultSet;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Zend\Db\Adapter\Driver\ResultInterface;

abstract class AbstractResultSet implements Iterator, ResultSetInterface
{
    /**
     * if -1, datasource is already buffered
     * if -2, implicitly disabling buffering in ResultSet
     * if false, explicitly disabled
     * if null, default state - nothing, but can buffer until iteration started
     * if array, already buffering
     * @var mixed
     */
    protected $buffer = null;

    /**
     * @var null|int
     */
    protected $count = null;

    /**
     * @var Iterator|IteratorAggregate|ResultInterface
     */
    protected $dataSource = null;

    /**
     * @var int
     */
    protected $fieldCount = null;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * Set the data source for the result set
     *
     * @param  array|Iterator|IteratorAggregate|ResultInterface $dataSource
     * @return ResultSet
     * @throws Exception\InvalidArgumentException
     */
    public function initialize($dataSource)
    {
        # echo '<br />initializing... @'.__LINE__.': '.__FILE__;
        // reset buffering
        if (is_array($this->buffer)) {
            $this->buffer = array();
        }

        if ($dataSource instanceof ResultInterface) {
            $this->count = $dataSource->count();
            $this->fieldCount = $dataSource->getFieldCount();
            $this->dataSource = $dataSource;
            if ($dataSource->isBuffered()) {
                $this->buffer = -1;
            }
            if (is_array($this->buffer)) {
                $this->dataSource->rewind();
            }
            # echo '<br />@'.__LINE__.': '.__FILE__;
            return $this;
        }

        if (is_array($dataSource)) {
            // its safe to get numbers from an array
            $first = current($dataSource);
            reset($dataSource);
            $this->count = count($dataSource);
            $this->fieldCount = count($first);
            $this->dataSource = new ArrayIterator($dataSource);
            $this->buffer = -1; // array's are a natural buffer
        } elseif ($dataSource instanceof IteratorAggregate) {
            $this->dataSource = $dataSource->getIterator();
        } elseif ($dataSource instanceof Iterator) {
            $this->dataSource = $dataSource;
        } else {
            throw new Exception\InvalidArgumentException('DataSource provided is not an array, nor does it implement Iterator or IteratorAggregate');
        }

        if ($this->count === null && $this->dataSource instanceof Countable) {
            $this->count = $this->dataSource->count();
        }

        # echo '<br />$this->dataSource: '.gettype($this->dataSource).'@'.__LINE__.': '.__FILE__;
        return $this;
    }

    public function buffer()
    {
        if ($this->buffer === -2) {
            throw new Exception\RuntimeException('Buffering must be enabled before iteration is started');
        } elseif ($this->buffer === null) {
            $this->buffer = array();
            if ($this->dataSource instanceof ResultInterface) {
                $this->dataSource->rewind();
            }
        }
        return $this;
    }

    public function isBuffered()
    {
        if ($this->buffer === -1 || is_array($this->buffer)) {
            return true;
        }
        return false;
    }

    /**
     * Get the data source used to create the result set
     *
     * @return null|Iterator
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Retrieve count of fields in individual rows of the result set
     *
     * @return int
     */
    public function getFieldCount()
    {
        if (null !== $this->fieldCount) {
            return $this->fieldCount;
        }

        $dataSource = $this->getDataSource();
        if (null === $dataSource) {
            return 0;
        }

        $dataSource->rewind();
        if (!$dataSource->valid()) {
            $this->fieldCount = 0;
            return 0;
        }

        $row = $dataSource->current();
        if (is_object($row) && $row instanceof Countable) {
            $this->fieldCount = $row->count();
            return $this->fieldCount;
        }

        $row = (array) $row;
        $this->fieldCount = count($row);
        return $this->fieldCount;
    }

    /**
     * Iterator: move pointer to next item
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        if ($this->buffer === null) {
            $this->buffer = -2; // implicitly disable buffering from here on
        }
        if (!is_array($this->buffer) || $this->position == $this->dataSource->key()) {
            $this->dataSource->next();
        }
        $this->position++;
    }

    /**
     * Iterator: retrieve current key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator: get current item
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if ($this->buffer === null) {
            $this->buffer = -2; // implicitly disable buffering from here on
        } elseif (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return $this->buffer[$this->position];
        }
        $data = $this->dataSource->current();
        if (is_array($this->buffer)) {
            $this->buffer[$this->position] = $data;
        }
        return $data;
    }

    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        if (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return true;
        }
        if ($this->dataSource instanceof Iterator) {
            # echo '<br />$this->dataSource->valid(): '.($this->dataSource->valid() ? 'valid': 'invalid | '.get_class($this->dataSource)).' - @'.__LINE__.': '.__FILE__;
            # if($this->dataSource instanceof \Zend\Db\Adapter\Driver\Pdo\Result) echo '<br />$this->dataSource->count(): '.$this->dataSource->count();
            return $this->dataSource->valid();
        } else {
            /**
             * modified by shkr dated 20230930 due to compatibility with php7.3
             */
            # echo '<br />@'.__LINE__.': '.__FILE__.'<br />gettype($this->dataSource): '.gettype($this->dataSource);
            if(is_null($this->dataSource)) return false;
            $key = key($this->dataSource);
            return ($key !== null);
        }
    }

    /**
     * Iterator: rewind
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        if (!is_array($this->buffer)) {
            /**
             * modified by shkr dated 20230930 due to compatibility with php7.3
             */
            if ($this->dataSource instanceof Iterator) {
                $this->dataSource->rewind();
            } elseif(!is_null($this->dataSource) && is_array($this->dataSource)) {
                reset($this->dataSource);
            }
        }
        $this->position = 0;
    }

    
    /**


/home/rocketstall/wms/vendor/ZF2/library/Zend/Db/ResultSet/AbstractResultSet.php:317 - count()
phar:///home/rocketstall/wms/vendor/ProjectLibraries/WMS.phar/ProductSearch/AdvancedSearch.php:49 - Zend\Db\ResultSet\AbstractResultSet\count()
phar:///home/rocketstall/wms/vendor/ProjectLibraries/WMS.phar/ProductSearch/SearchAbstract.php:1399 - WMS\ProductSearch\AdvancedSearch\getInitialFilterArray()
phar:///home/rocketstall/wms/vendor/ProjectLibraries/WMS.phar/ProductSearch/AdvancedSearch.php:16 - WMS\ProductSearch\SearchAbstract\setSql(SingleProduct.singleProductType='full' and ((SingleProduct.customerCategoryId in (4912,4932,4926,4925,4924,4923,4930,4927,4928,4933,4938,4939,4940,4929,4931) or SingleProductCategory.customerCategoryId in (4912,4932,4926,4925,4924,4923,4930,4927,4928,4933,4938,4939,4940,4929,4931))))
phar:///home/rocketstall/wms/vendor/ProjectLibraries/WMS.phar/ProductSearch/SearchAbstract.php:1070 - WMS\ProductSearch\AdvancedSearch\setSql(SingleProduct.singleProductType='full' and ((SingleProduct.customerCategoryId in (4912,4932,4926,4925,4924,4923,4930,4927,4928,4933,4938,4939,4940,4929,4931) or SingleProductCategory.customerCategoryId in (4912,4932,4926,4925,4924,4923,4930,4927,4928,4933,4938,4939,4940,4929,4931))))
/home/rocketstall/wms/cart/module/BpeProducts/src/BpeProducts/Controller/BpeProductsController.php:1299 - WMS\ProductSearch\SearchAbstract\setManufacturerBrandAndCategory(, , )
/home/rocketstall/wms/cart/module/BpeProducts/src/BpeProducts/Controller/BpeProductsController.php:1292 - BpeProducts\Controller\BpeProductsController\setSearchInstanceByManufacturerBrandAndCategory(, , )
/home/rocketstall/wms/cart/module/BpeProducts/src/BpeProducts/Controller/BpeProductsController.php:1133 - BpeProducts\Controller\BpeProductsController\setSearchInstanceByCategory()
/home/rocketstall/wms/vendor/ZF2/library/Zend/Mvc/Controller/AbstractActionController.php:82 - BpeProducts\Controller\BpeProductsController\categoriesAction()
NO_FILE:NO_LINE - Zend\Mvc\Controller\AbstractActionController\onDispatch()
/home/rocketstall/wms/vendor/ZF2/library/Zend/EventManager/EventManager.php:444 - call_user_func(, )
/home/rocketstall/wms/vendor/ZF2/library/Zend/EventManager/EventManager.php:205 - Zend\EventManager\EventManager\triggerListeners(dispatch, , )
/home/rocketstall/wms/vendor/ZF2/library/Zend/Mvc/Controller/AbstractController.php:118 - Zend\EventManager\EventManager\trigger(dispatch, , )
/home/rocketstall/wms/vendor/ZF2/library/Zend/Mvc/DispatchListener.php:93 - Zend\Mvc\Controller\AbstractController\dispatch(, )
NO_FILE:NO_LINE - Zend\Mvc\DispatchListener\onDispatch()
/home/rocketstall/wms/vendor/ZF2/library/Zend/EventManager/EventManager.php:444 - call_user_func(, )
/home/rocketstall/wms/vendor/ZF2/library/Zend/EventManager/EventManager.php:205 - Zend\EventManager\EventManager\triggerListeners(dispatch, , )
/home/rocketstall/wms/vendor/ZF2/library/Zend/Mvc/Application.php:314 - Zend\EventManager\EventManager\trigger(dispatch, , )
/home/rocketstall/public_html/hosted-wms-carts/index.php:61 - Zend\Mvc\Application\run()
Unknown error type: [2] count(): Parameter must be an array or an object that implements Countable


     */
    
    
    /**
     * Countable: return count of rows
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        if ($this->count !== null) {
            return $this->count;
        }
        #die('@'.__LINE__.': '.__FILE__.' - '.gettype($this->dataSource));
        /**
         * modified by shkr dated 20230930 due to compatibility with php7.3
         */
        if(is_null($this->dataSource)) return 0;
        if(!is_array($this->dataSource) || !($this->dataSource instanceof \Iterator)){
            if($this->dataSource instanceof \Countable) return $this->dataSource->count();
            return 0;
        }
        $this->count = count($this->dataSource);
        return $this->count;
    }

    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray()
    {
        $return = array();
        foreach ($this as $row) {
            if (is_array($row)) {
                $return[] = $row;
            } elseif (method_exists($row, 'toArray')) {
                $return[] = $row->toArray();
            } elseif (method_exists($row, 'getArrayCopy')) {
                $return[] = $row->getArrayCopy();
            } else {
                throw new Exception\RuntimeException(
                    'Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array'
                );
            }
        }
        return $return;
    }
}
