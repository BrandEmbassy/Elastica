<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules([
        '@PHP71Migration' => true,
        '@PhpCsFixer' => true,
        '@PHPUnit75Migration:risky' => true,
        '@PSR2' => true,
        '@Symfony' => true,
        'is_null' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'native_function_invocation' => false,
        'no_alias_functions' => true,
        'no_useless_else' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_imports' => true,
        'php_unit_dedicate_assert' => ['target' => 'newest'],
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_order' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
        'static_lambda' => true,
        'ternary_to_null_coalescing' => true,
        'visibility_required' => ['elements' => ['property', 'method', 'const']],
        'phpdoc_no_useless_inheritdoc' => false,
        'no_superfluous_phpdoc_tags' => false,
        'global_namespace_import' => false,
        'trailing_comma_in_multiline' => false,
        'fully_qualified_strict_types' => false,
        'phpdoc_separation' => false,
        'new_with_parentheses' => false,
        'escape_implicit_backslashes' => false,
        'declare_strict_types' => false,
        'no_unneeded_control_parentheses' => false,
        'string_implicit_backslashes' => false,
        'phpdoc_no_alias_tag' => ['replacements' => ['type' => 'var', 'link' => 'see']],
        'yoda_style' => false,
        'curly_braces_position' => false,
        'braces_position' => false,
        'no_extra_blank_lines' => false,
        'blank_line_after_opening_tag' => false,
        'cast_spaces' => false,
        'native_constant_invocation' => false,
        'single_line_throw' => false,
        'declare_parentheses' => false,
        'class_attributes_separation' => false,
        'linebreak_after_opening_tag' => false,
        'declare_equal_normalize' => false,
        'blank_line_between_import_groups' => false,
        'single_quote' => false,
        'ordered_class_elements' => false,
    ])
;
