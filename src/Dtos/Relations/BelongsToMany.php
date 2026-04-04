<?php

declare(strict_types=1);

namespace Brackets\AdminGenerator\Dtos\Relations;

final readonly class BelongsToMany
{
    public function __construct(
        public string $currentTable,
        public string $relatedTable,
        public string $relatedModel,
        public string $relatedModelName,
        public string $relatedModelNamePlural,
        public string $relatedModelVariableName,
        public string $relationTable,
        public string $foreignKey,
        public string $relatedKey,
        public string $relatedLabel,
    ) {
    }

    /** @deprecated just for compatibility with old code */
    public function toLegacyArray(): array
    {
        return [
            'current_table' => $this->currentTable,
            'related_table' => $this->relatedTable,
            'related_model' => $this->relatedModel,
            'related_model_name' => $this->relatedModelName,
            'related_model_name_plural' => $this->relatedModelNamePlural,
            'related_model_variable_name' => $this->relatedModelVariableName,
            'relation_table' => $this->relationTable,
            'foreign_key' => $this->foreignKey,
            'related_key' => $this->relatedKey,
            'related_label' => $this->relatedLabel,
        ];
    }
}
