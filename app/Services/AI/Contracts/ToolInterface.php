<?php

namespace App\Services\AI\Contracts;

use App\Models\User;

interface ToolInterface
{
    /**
     * Unique identifier for the function call (e.g., 'book_appointment').
     */
    public function name(): string;

    /**
     * Human and model-readable explanation of what the tool accomplishes.
     */
    public function description(): string;

    /**
     * OpenAPI / Google Gemini Schema declaration of expected input parameters.
     *
     * @return array<string, mixed>
     */
    public function parameters(): array;

    /**
     * Array of roles permitted to invoke this tool.
     * Allowed values: 'patient', 'doctor', 'staff', 'admin', 'superadmin', 'driver'.
     *
     * @return array<string>
     */
    public function allowedRoles(): array;

    /**
     * Execute the tool with zero-trust validation and role context.
     *
     * @param array<string, mixed> $arguments
     * @param User $authenticatedUser
     * @return array<string, mixed>
     */
    public function execute(array $arguments, User $authenticatedUser): array;
}
