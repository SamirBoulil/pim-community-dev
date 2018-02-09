<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Common;

/**
 * Explode a list of code like 'EUR, USD'.
 */
class ListOfCodes
{
    /** @var string */
    private $listOfCode;

    public function __construct(string $listOfCode)
    {
        $this->listOfCode = $listOfCode;
    }

    /**
     * @return array
     */
    public function explode(string $separator = ',')
    {
        $codes = explode($separator, $this->listOfCode);

        return array_map('trim', $codes);
    }
}