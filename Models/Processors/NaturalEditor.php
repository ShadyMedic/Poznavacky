<?php

namespace Poznavacky\Models\Processors;

use Poznavacky\Models\DatabaseItems\ClassObject;
use Poznavacky\Models\DatabaseItems\Natural;
use Poznavacky\Models\Exceptions\AccessDeniedException;
use Poznavacky\Models\Security\DataValidator;
use \InvalidArgumentException;
use \RangeException;

/**
 * Model zpracovávající změny přírodnin odeslané ze stránky naturals
 * Třída slouží spíše jako mezičlánek provádějící veškerá ověření
 * @author Jan Štěch
 */
class NaturalEditor
{
    private $class;
    private $idsOfNaturalsInClass;

    /**
     * Konstruktor modelu nastavující objekt třídy, v níž je dovoleno upravovat přírodniny a získávající seznam jejich ID
     * @param ClassObject $class Objekt třídy, která má být pomocí tohoto objektu spravována
     */
    public function __construct(ClassObject $class)
    {
        $this->class = $class;
        $this->idsOfNaturalsInClass = array_map(function($item) { return $item->getId(); }, $class->getNaturals());
    }

    public function rename(Natural $natural, string $newName): bool
    {
        //Zkontroluj, zda je název přírodniny platný
        $validator = new DataValidator();
        try
        {
            $validator->checkLength($newName, DataValidator::NATURAL_NAME_MIN_LENGTH, DataValidator::NATURAL_NAME_MAX_LENGTH, DataValidator::TYPE_NATURAL_NAME);
            $validator->checkCharacters($newName, DataValidator::NATURAL_NAME_ALLOWED_CHARS, DataValidator::TYPE_NATURAL_NAME);
        }
        catch (RangeException $e)
        {
            switch ($e->getMessage())
            {
                case 'short':
                    throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NATURALS_RENAME_NAME_TO_SHORT, null, $e);
                    break;
                case 'long':
                    throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NATURALS_RENAME_NAME_TO_LONG, null, $e);
                    break;
            }
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NATURALS_RENAME_INVALID_CHARACTERS, null, $e);
        }

        try
        {
            $validator->checkUniqueness($newName, DataValidator::TYPE_NATURAL_NAME, $this->class);
        }
        catch (InvalidArgumentException $e)
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NATURALS_RENAME_DUPLICATE_NAME);
        }

        if (!in_array($natural->getId(), $this->idsOfNaturalsInClass))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NATURALS_RENAME_FOREIGN_NATURAL);
        }

        $natural->rename($newName);
        return $natural->save();
    }

    public function merge(Natural $from, Natural $to): array
    {
        if (!in_array($from->getId(), $this->idsOfNaturalsInClass))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NATURALS_MERGE_FROM_FOREIGN_NATURAL);
        }
        if (!in_array($to->getId(), $this->idsOfNaturalsInClass))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NATURALS_MERGE_TO_FOREIGN_NATURAL);
        }

        return $from->merge($to);
    }

    public function delete(Natural $natural): bool
    {
        if (!in_array($natural->getId(), $this->idsOfNaturalsInClass))
        {
            throw new AccessDeniedException(AccessDeniedException::REASON_MANAGEMENT_NATURALS_DELETE_FOREIGN_NATURAL);
        }

        return $natural->delete();
    }
}