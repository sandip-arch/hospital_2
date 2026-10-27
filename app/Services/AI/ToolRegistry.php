<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use App\Services\AI\Tools\AdmitPatientTool;
use App\Services\AI\Tools\BookAmbulanceDispatchTool;
use App\Services\AI\Tools\BookAppointmentTool;
use App\Services\AI\Tools\CancelAppointmentTool;
use App\Services\AI\Tools\CheckBedAvailabilityTool;
use App\Services\AI\Tools\DischargePatientTool;
use App\Services\AI\Tools\DiscoverNearbyAmbulancesTool;
use App\Services\AI\Tools\DriverDutyAndVehicleTool;
use App\Services\AI\Tools\ExplainMedicineTool;
use App\Services\AI\Tools\GetInvoicesTool;
use App\Services\AI\Tools\GetLabReportsTool;
use App\Services\AI\Tools\GetMyAppointmentsTool;
use App\Services\AI\Tools\GetPatientMedicalRecordsTool;
use App\Services\AI\Tools\GetPrescriptionsTool;
use App\Services\AI\Tools\HospitalServicesCatalogTool;
use App\Services\AI\Tools\LookupDoctorsTool;
use App\Services\AI\Tools\ManageDoctorScheduleTool;
use App\Services\AI\Tools\OrderLabTestTool;
use App\Services\AI\Tools\QueryDoctorAvailableSlotsTool;
use App\Services\AI\Tools\RecordPaymentReceiptTool;
use App\Services\AI\Tools\RescheduleAppointmentTool;
use App\Services\AI\Tools\SubmitAmbulanceComplaintTool;
use App\Services\AI\Tools\SwitchBedTool;
use App\Services\AI\Tools\TrackAmbulanceBookingTool;
use App\Services\AI\Tools\WritePrescriptionTool;
use Exception;

class ToolRegistry
{
    /**
     * @var array<string, ToolInterface>
     */
    protected array $tools = [];

    public function __construct()
    {
        $this->registerDefaultTools();
    }

    /**
     * Register all available domain tools.
     */
    protected function registerDefaultTools(): void
    {
        $toolClasses = [
            // Outpatient & Appointments
            LookupDoctorsTool::class,
            QueryDoctorAvailableSlotsTool::class,
            BookAppointmentTool::class,
            CancelAppointmentTool::class,
            RescheduleAppointmentTool::class,
            GetMyAppointmentsTool::class,

            // Emergency & Ambulance Fleet
            DiscoverNearbyAmbulancesTool::class,
            BookAmbulanceDispatchTool::class,
            TrackAmbulanceBookingTool::class,

            // Clinical EMR & Pharmacy (RAG)
            GetPrescriptionsTool::class,
            ExplainMedicineTool::class,
            WritePrescriptionTool::class,
            GetPatientMedicalRecordsTool::class,

            // Inpatient ADT & Diagnostics
            CheckBedAvailabilityTool::class,
            AdmitPatientTool::class,
            SwitchBedTool::class,
            DischargePatientTool::class,
            GetLabReportsTool::class,
            OrderLabTestTool::class,

            // Finance & Front Desk
            GetInvoicesTool::class,
            RecordPaymentReceiptTool::class,
            HospitalServicesCatalogTool::class,

            // Physician & Driver Fleet Operations
            ManageDoctorScheduleTool::class,
            DriverDutyAndVehicleTool::class,
            SubmitAmbulanceComplaintTool::class,
        ];

        foreach ($toolClasses as $class) {
            $instance = new $class();
            $this->tools[$instance->name()] = $instance;
        }
    }

    /**
     * Retrieve all tools filtered strictly for the authenticated user's role.
     *
     * @param User $user
     * @return array<string, ToolInterface>
     */
    public function getToolsForUser(User $user): array
    {
        $filtered = [];

        foreach ($this->tools as $name => $tool) {
            if (RelationshipGuard::userHasAllowedRole($user, $tool->allowedRoles())) {
                $filtered[$name] = $tool;
            }
        }

        return $filtered;
    }

    /**
     * Generate Gemini Function Declarations payload strictly filtered for user.
     *
     * @param User $user
     * @return array<array<string, mixed>>
     */
    public function getGeminiFunctionDeclarations(User $user): array
    {
        $userTools = $this->getToolsForUser($user);
        $declarations = [];

        foreach ($userTools as $tool) {
            $declarations[] = [
                'name' => $tool->name(),
                'description' => $tool->description(),
                'parameters' => $tool->parameters(),
            ];
        }

        return $declarations;
    }

    /**
     * Execute a tool by name with zero-trust validation.
     *
     * @param string $name
     * @param array<string, mixed> $arguments
     * @param User $user
     * @return array<string, mixed>
     */
    public function executeTool(string $name, array $arguments, User $user): array
    {
        if (!isset($this->tools[$name])) {
            return [
                'status' => 'error',
                'message' => "Unrecognized tool action '{$name}'.",
            ];
        }

        $tool = $this->tools[$name];

        // Strict Role Check before execution
        if (!RelationshipGuard::userHasAllowedRole($user, $tool->allowedRoles())) {
            return [
                'status' => 'error',
                'message' => "Access Denied: Your account role does not have permission to execute '{$name}'.",
            ];
        }

        try {
            return $tool->execute($arguments, $user);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => "Tool execution failed: " . $e->getMessage(),
            ];
        }
    }
}
