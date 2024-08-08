<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */

declare(strict_types=1);

namespace Amasty\Checkout\Model\Optimization;

use Amasty\Checkout\Block\Onepage\LayoutWalker;
use Amasty\Checkout\Block\Onepage\LayoutWalkerFactory;

/**
 * Layout JS styled difference array processor.
 * @since 3.0.10
 */
class LayoutJsDiffProcessor
{
    /**
     * Diff properties
     */
    const KEY_ACTION = 'a';
    const KEY_VALUE = 'v';
    const KEY_MOVE_TO_POSITION = 'p';

    /**
     * Actions for diff
     */
    const ACTION_REMOVE = 'rm';
    const ACTION_ADD = 'add';
    const ACTION_CHANGE = 'ch';

    /**
     * @var LayoutWalkerFactory
     */
    private $layoutWalkerFactory;

    public function __construct(LayoutWalkerFactory $layoutWalkerFactory)
    {
        $this->layoutWalkerFactory = $layoutWalkerFactory;
    }

    /**
     * Compare arrays and return styled difference array.
     *
     * @param array $originArray
     * @param array $newArray
     * @param null|string $path dot separated array key path
     *
     * @return array = [<path> => ['a' => 'action', 'v' => mixed], ...]
     */
    public function createFlatDiff(array $originArray, array $newArray, ?string $path = ''): array
    {
        $diff = [];
        $path = $path ? $path . '.' : '';

        foreach ($newArray as $key => $value) {
            $fullPath = $this->compressPath($path, $key);
            if (array_key_exists($key, $originArray)) {
                if ($value !== $originArray[$key]) {
                    if (is_array($value) && is_array($originArray[$key])) {
                        $diff += $this->createFlatDiff($originArray[$key], $value, $fullPath);
                    } else {
                        $diff[$fullPath] = [self::KEY_ACTION => self::ACTION_CHANGE, self::KEY_VALUE => $value];
                    }
                }
            } else {
                $diff[$fullPath] = [self::KEY_ACTION => self::ACTION_ADD, self::KEY_VALUE => $value];
            }
        }

        $this->moveRemoveCalculate($originArray, $newArray, $path, $diff);

        return $diff;
    }

    /**
     * Calculate actions Move and Remove
     *
     * @param array $originArray
     * @param array $newArray
     * @param string $path
     * @param array $diff
     */
    private function moveRemoveCalculate(array $originArray, array $newArray, string $path, array &$diff): void
    {
        $originPosition = 0;
        $newArrayKeys = array_keys($newArray);
        $movedKeys = [];
        foreach ($originArray as $key => &$value) {
            if (!array_key_exists($key, $newArray)) {
                $fullPath = $this->compressPath($path, $key);
                $diff[$fullPath] = [self::KEY_ACTION => self::ACTION_REMOVE];
            } else {
                $newPosition = array_search($key, $newArrayKeys);
                $originRelativePosition = $this->calculateRelativePosition($movedKeys, $originPosition, $newPosition);

                if ($originRelativePosition > $newPosition && $newPosition > 0) {
                    //for move up in array, better to paste before designated position
                    --$newPosition;
                }

                if ($originRelativePosition !== $newPosition) {
                    $movedKeys[$key] = ['new' => $newPosition, 'origin' => $originRelativePosition];
                }
                ++$originPosition;
            }
        }

        foreach ($movedKeys as $key => $movedPosition) {
            $fullPath = $this->compressPath($path, $key);
            $diff[$fullPath][self::KEY_MOVE_TO_POSITION] = $movedPosition['new'];
        }
    }

    /**
     * Calculate relative position
     * @param array $movedElements
     * @param int $originRelativePosition
     * @param int $newPosition
     *
     * @return int
     */
    private function calculateRelativePosition(array $movedElements, int $originRelativePosition, int $newPosition): int
    {
        foreach ($movedElements as $element) {
            if ($element['new'] > $element['origin']
                && $element['new'] > $newPosition
                && $element['origin'] <= $newPosition
            ) {
                //Move execution works one by one, that's why should be relative position
                --$originRelativePosition;
            }
        }

        return $originRelativePosition;
    }

    /**
     * Replace path parts with template for compress result.
     *
     * @param string $parentPath
     * @param string|int $currentKey
     *
     * @return string
     */
    private function compressPath(string $parentPath, $currentKey): string
    {
        $currentKey = str_replace('.', LayoutWalker::ESCAPED_SEPARATOR, $currentKey);
        $keyPath = $parentPath . $currentKey;
        $keyPath = str_replace(['.children'], ['.>>'], $keyPath);

        $pathTemplates = array_reverse(LayoutWalker::LAYOUT_PATH_TEMPLATES, true);

        foreach ($pathTemplates as $template => $fullPath) {
            $keyPath = str_replace($fullPath . '.', $template . '.', $keyPath);
        }

        return $keyPath;
    }

    /**
     * Apply styled difference array to layout.
     *
     * @param array $originArray jsLayout
     * @param array $diff styled flat array of jsLayout Difference
     *
     * @return array
     */
    public function applyDiffToArray(array $originArray, array $diff): array
    {
        /** @var LayoutWalker $layoutWalker */
        $layoutWalker = $this->layoutWalkerFactory->create(['layoutArray' => $originArray]);
        foreach ($diff as $path => $valueProperties) {
            if (isset($valueProperties[self::KEY_ACTION])) {
                switch ($valueProperties[self::KEY_ACTION]) {
                    case self::ACTION_ADD:
                    case self::ACTION_CHANGE:
                        $layoutWalker->setValue($path, $valueProperties[self::KEY_VALUE]);
                        break;
                    case self::ACTION_REMOVE:
                        $layoutWalker->unsetByPath($path);
                        break;
                }
            }
            if (isset($valueProperties[self::KEY_MOVE_TO_POSITION])) {
                $this->moveArray($layoutWalker, $path, $valueProperties);
            }
        }

        return $layoutWalker->getResult();
    }

    /**
     * Process Move action.
     *
     * @param LayoutWalker $layoutWalker
     * @param string $path
     * @param array $valueProperties
     */
    private function moveArray(LayoutWalker &$layoutWalker, string $path, array $valueProperties): void
    {
        $parentPath = $layoutWalker->parseArrayPath($path);
        $moveKey = array_pop($parentPath);
        $parentPath = implode('.', $parentPath);
        $layoutValue = $layoutWalker->getValue($parentPath);
        $movedArray = [];
        $currentPosition = 0;
        foreach ($layoutValue as $key => $childValue) {
            if ($key !== $moveKey) {
                $movedArray[$key] = $childValue;
            }
            if ($valueProperties[self::KEY_MOVE_TO_POSITION] === $currentPosition++) {
                $movedArray[$moveKey] = $layoutValue[$moveKey];
            }
        }
        $layoutWalker->setValue($parentPath, $movedArray);
    }
}
