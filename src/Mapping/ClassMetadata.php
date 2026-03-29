<?php declare(strict_types=1);

namespace Oryx\ORM\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;

class ClassMetadata
{
    private DoctrineClassMetadata $doctrineMetadata;

    public function __construct(DoctrineClassMetadata $doctrineMetadata)
    {
        $this->doctrineMetadata = $doctrineMetadata;
    }

    public function getName(): string
    {
        return $this->doctrineMetadata->getName();
    }

    public function isRootEntity(): bool
    {
        return $this->doctrineMetadata->isRootEntity();
    }

    public function getFieldNames(): array
    {
        return $this->doctrineMetadata->getFieldNames();
    }

    public function getAssociationNames(): array
    {
        return $this->doctrineMetadata->getAssociationNames();
    }

    public function getIdentifier(): array
    {
        return $this->doctrineMetadata->getIdentifier();
    }

    public function getFieldMapping(string $fieldName): array
    {
        return $this->doctrineMetadata->getFieldMapping($fieldName);
    }

    public function hasField(string $fieldName): bool
    {
        return $this->doctrineMetadata->hasField($fieldName);
    }

    public function hasAssociation(string $associationName): bool
    {
        return $this->doctrineMetadata->hasAssociation($associationName);
    }

    public function getAssociationTargetClass(string $associationName): string
    {
        return $this->doctrineMetadata->getAssociationTargetClass($associationName);
    }

    public function isAssociationInverseSide(string $associationName): bool
    {
        return $this->doctrineMetadata->isAssociationInverseSide($associationName);
    }

    public function getAssociationMappedByTargetField(string $associationName): string
    {
        return $this->doctrineMetadata->getAssociationMappedByTargetField($associationName);
    }

    public function isCollectionValuedAssociation(string $associationName): bool
    {
        return $this->doctrineMetadata->isCollectionValuedAssociation($associationName);
    }

    public function getTableName(): string
    {
        return $this->doctrineMetadata->getTableName();
    }

    public function getSchemaName(): string
    {
        return $this->doctrineMetadata->getSchemaName();
    }

    public function getIdentifierValues($entity): array
    {
        return $this->doctrineMetadata->getIdentifierValues($entity);
    }

    public function getIdentifierColumnNames(): array
    {
        return $this->doctrineMetadata->getIdentifierColumnNames();
    }

    public function isIdentifier(string $fieldName): bool
    {
        return $this->doctrineMetadata->isIdentifier($fieldName);
    }

    public function getDiscriminatorColumn(): array
    {
        return $this->doctrineMetadata->getDiscriminatorColumn();
    }

    public function isIdentifierInVersion(string $fieldName): bool
    {
        return $this->doctrineMetadata->isIdentifier($fieldName);
    }

    public function isInheritanceTypeNone(): bool
    {
        return $this->doctrineMetadata->isInheritanceTypeNone();
    }

    public function isInheritanceTypeJoined(): bool
    {
        return $this->doctrineMetadata->isInheritanceTypeJoined();
    }

    public function isInheritanceTypeSingleTable(): bool
    {
        return $this->doctrineMetadata->isInheritanceTypeSingleTable();
    }

    public function isInheritanceTypeTablePerClass(): bool
    {
        return $this->doctrineMetadata->isInheritanceTypeTablePerClass();
    }

    public function setInheritanceType(int $type): void
    {
        $this->doctrineMetadata->setInheritanceType($type);
    }
}