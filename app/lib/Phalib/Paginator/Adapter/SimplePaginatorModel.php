<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2017 Phalcon Team (https://phalconphp.com)          |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  |          Vladimir Metelitsa <green.cat@me.com>                         |
  +------------------------------------------------------------------------+
 */

namespace Phalib\Paginator\Adapter;

use Phalcon\Paginator\Adapter\AbstractAdapter;
use Phalcon\Paginator\Exception;
use Phalcon\Paginator\RepositoryInterface;
use SeekableIterator;

/**
 * Phalib\Paginator\Adapter\SimplePaginatorModel
 *
 * This adapter allows to paginate data using a Phalcon\Mvc\Model resultset as a base.
 *
 * <code>
 * use Phalib\Paginator\Adapter\SimplePaginatorModel;
 *
 * $paginator = new SimplePaginatorModel(
 *     [
 *         "data"  => Robots::find(),
 *         "limit" => 25,
 *         "page"  => $currentPage,
 *     ]
 * );
 *
 * $paginate = $paginator->paginate();
 * </code>
 */
class SimplePaginatorModel extends AbstractAdapter {

    public function __construct(array $config) {
        $this->config = $config;

        if ($config["limit"]) {
            $this->limitRows = (int) $config["limit"];
        }

        if ($config["page"]) {
            $this->page = (int) $config["page"];
        }
    }

    /**
     * Returns a slice of the resultset to show in the pagination
     */
    public function paginate(): RepositoryInterface {
        $show = (int) $this->limitRows;
        $config = $this->config;

        /** @var SeekableIterator $items */
        $items = $config["data"];
        $page_number = (int) $this->page;

        if (!is_object($items)) {
            throw new Exception("Invalid data for paginator");
        }

        // Prevents 0 or negative page numbers
        if ($page_number <= 0) {
            $page_number = 1;
        }

        // Prevents a limit creating a negative or zero first page
        if ($show <= 0) {
            throw new Exception("The start page number is zero or less");
        }

        $n = count($items);
        $last_show_page = $page_number - 1;
        $start = $show * $last_show_page;
        $page_items = [];

        if ($n % $show != 0) {
            $total_pages = (int) ($n / $show + 1);
        } else {
            $total_pages = (int) ($n / $show);
        }

        if ($n > 0) {
            // Seek to the desired position
            if ($start <= $n) {
                $items->seek($start);
            } else {
                $items->seek(0);
                $page_number = 1;
            }

            // The record must be iterable
            $i = 1;
            while ($items->valid()) {
                $page_items[] = $items->current();

                if ($i >= $show) {
                    break;
                }

                $i++;
                $items->next();
            }
        }

        // Fix next
        $next = $page_number + 1;
        if ($next > $total_pages) {
            $next = $total_pages;
        }

        if ($page_number > 1) {
            $before = $page_number - 1;
        } else {
            $before = 1;
        }

        return $this->getRepository(
                        [
                            RepositoryInterface::PROPERTY_ITEMS => $page_items,
                            RepositoryInterface::PROPERTY_TOTAL_ITEMS => $n,
                            RepositoryInterface::PROPERTY_LIMIT => $this->limitRows,
                            RepositoryInterface::PROPERTY_FIRST_PAGE => 1,
                            RepositoryInterface::PROPERTY_PREVIOUS_PAGE => $before,
                            RepositoryInterface::PROPERTY_CURRENT_PAGE => $page_number,
                            RepositoryInterface::PROPERTY_NEXT_PAGE => $next,
                            RepositoryInterface::PROPERTY_LAST_PAGE => $total_pages
                        ]
        );
    }

}
