<?php

declare(strict_types = 1);

namespace Poppy\Framework\Classes;

/**
 * This file is part of the PHPLucidFrame library.
 * The class makes you easy to build console style tables
 *
 * @since       PHPLucidFrame v 1.12.0
 * @copyright   Copyright (c), PHPLucidFrame.
 * @author      Sithu K. <cithukyaw@gmail.com>
 * @link        http://phplucidframe.github.io
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE
 */
class ConsoleTable
{
    const HEADER_INDEX = -1;
    const HR           = 'HR';

    /**
     * Array of table data
     * @var array
     */
    protected array $data = [];

    /**
     * Border shown or not
     * @var boolean
     */
    protected bool $border = true;

    /**
     *  All borders shown or not
     * @var boolean
     */
    protected bool $allBorders = false;

    /**
     *  Table padding
     * @var integer
     */
    protected int $padding = 1;

    /**
     * Table left margin
     * @var integer
     */
    protected int $indent = 0;

    /**
     * @var integer
     */
    private int $rowIndex = -1;

    /**
     * @var array
     */
    private array $columnWidths = [];


    /**
     * Set headers for the columns in one-line
     * @param array $content Array of header cell content
     * @return self
     */
    public function headers(array $content): self
    {
        $this->data[self::HEADER_INDEX] = $content;

        return $this;
    }


    public function rows(array $rows): self
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
        return $this;
    }

    /**
     * Get the row of header
     */
    public function getHeaders()
    {
        return $this->data[self::HEADER_INDEX] ?? null;
    }

    /**
     * Show table border
     * @return self
     */
    public function showBorder(): self
    {
        $this->border = true;

        return $this;
    }

    /**
     * Hide table border
     * @return self
     */
    public function hideBorder(): self
    {
        $this->border = false;

        return $this;
    }

    /**
     * Show all table borders
     * @return self
     */
    public function showAllBorders(): self
    {
        $this->showBorder();
        $this->allBorders = true;

        return $this;
    }

    /**
     * Set padding for each cell
     * @param int $value The integer value, defaults to 1
     * @return self
     */
    public function setPadding(int $value = 1): self
    {
        $this->padding = $value;

        return $this;
    }

    /**
     * Set left indentation for the table
     * @param int $value The integer value, defaults to 1
     * @return self
     */
    public function setIndent(int $value = 0): self
    {
        $this->indent = $value;

        return $this;
    }

    /**
     * Add horizontal border line
     * @return self
     */
    public function addBorderLine(): self
    {
        $this->rowIndex++;
        $this->data[$this->rowIndex] = self::HR;

        return $this;
    }

    /**
     * Print the table
     * @return void
     */
    public function display()
    {
        echo $this->getTable();
    }

    /**
     * Adds a row to the table
     * @param array|null $data The row data to add
     */
    private function addRow(array $data = null): void
    {
        $this->rowIndex++;

        if (is_array($data)) {
            foreach ($data as $col => $content) {
                $this->data[$this->rowIndex][$col] = $content;
            }
        }
    }

    /**
     * Get the printable table content
     * @return string
     */
    private function getTable(): string
    {
        $this->calculateColumnWidth();

        $output = $this->border ? $this->getBorderLine() : '';
        foreach ($this->data as $y => $row) {
            if ($row === self::HR) {
                if (!$this->allBorders) {
                    $output .= $this->getBorderLine();
                    unset($this->data[$y]);
                }

                continue;
            }

            foreach ($row as $x => $cell) {
                $output .= $this->getCellOutput($x, $row);
            }
            $output .= PHP_EOL;

            if ($y === self::HEADER_INDEX) {
                $output .= $this->getBorderLine();
            }
            else {
                if ($this->allBorders) {
                    $output .= $this->getBorderLine();
                }
            }
        }

        if (!$this->allBorders) {
            $output .= $this->border ? $this->getBorderLine() : '';
        }

        if (PHP_SAPI !== 'cli') {
            $output = '<pre>' . $output . '</pre>';
        }

        return $output;
    }

    /**
     * Get the printable borderline
     * @return string
     */
    private function getBorderLine(): string
    {
        $output = '';

        if (isset($this->data[0])) {
            $columnCount = count($this->data[0]);
        }
        elseif (isset($this->data[self::HEADER_INDEX])) {
            $columnCount = count($this->data[self::HEADER_INDEX]);
        }
        else {
            return $output;
        }

        for ($col = 0; $col < $columnCount; $col++) {
            $output .= $this->getCellOutput($col);
        }

        if ($this->border) {
            $output .= '+';
        }
        $output .= PHP_EOL;

        return $output;
    }

    /**
     * Get the printable cell content
     *
     * @param integer    $index The column index
     * @param array|null $row   The table row
     * @return string
     */
    private function getCellOutput(int $index, array $row = null): string
    {
        $cell    = $row ? $row[$index] : '-';
        $width   = $this->columnWidths[$index];
        $padding = str_repeat($row ? ' ' : '-', $this->padding);

        $output = '';

        if ($index === 0) {
            $output .= str_repeat(' ', $this->indent);
        }

        if ($this->border) {
            $output .= $row ? '|' : '+';
        }

        $output  .= $padding;                               # left padding
        $cell    = trim(preg_replace('/\s+/', ' ', $cell)); # remove line breaks
        $content = preg_replace('#\x1b[][^A-Za-z]*[A-Za-z]#', '', $cell);
        $delta   = mb_strlen($cell, 'UTF-8') - mb_strlen($content, 'UTF-8');
        $output  .= $this->strPadUnicode($cell, $width + $delta, $row ? ' ' : '-'); # cell content
        $output  .= $padding;                                                       # right padding
        if ($row && $index == count($row) - 1 && $this->border) {
            $output .= '|';
        }

        return $output;
    }

    /**
     * Calculate maximum width of each column
     * @return void
     */
    private function calculateColumnWidth(): void
    {
        foreach ($this->data as $row) {
            if (is_array($row)) {
                foreach ($row as $x => $col) {
                    $content = preg_replace('#\x1b[][^A-Za-z]*[A-Za-z]#', '', $col);
                    if (!isset($this->columnWidths[$x])) {
                        $this->columnWidths[$x] = mb_strlen($content, 'UTF-8');
                    }
                    else {
                        if (mb_strlen($content, 'UTF-8') > $this->columnWidths[$x]) {
                            $this->columnWidths[$x] = mb_strlen($content, 'UTF-8');
                        }
                    }
                }
            }
        }

    }

    /**
     * Multibyte version of str_pad() function
     * @source http://php.net/manual/en/function.str-pad.php
     */
    private function strPadUnicode($str, $padLength = 5, $padString = ' ')
    {
        $strLen    = mb_strlen($str, 'UTF-8');
        $padStrLen = mb_strlen($padString, 'UTF-8');

        if (!$strLen) {
            $strLen = 1;
        }

        if (!$padLength || !$padStrLen || $padLength <= $strLen) {
            return $str;
        }


        $repeat = (int) ceil($strLen - $padStrLen + $padLength);

        $result = $str . str_repeat($padString, $repeat);
        return mb_substr($result, 0, $padLength, 'UTF-8');
    }
}