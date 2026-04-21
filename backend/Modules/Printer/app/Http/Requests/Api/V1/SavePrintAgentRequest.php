<?php

namespace Modules\Printer\Http\Requests\Api\V1;

use Modules\Core\Http\Requests\Request;
use Closure;

class SavePrintAgentRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            ...$this->getTranslationRules(["name" => "required|max:255"]),
            ...$this->getBranchRule(),
            "is_active" => "required|boolean",
            "api_key" => "required|string|max:255",
            "host" => [
                "required",
                "string",
                "max:255",
                function (string $attribute, mixed $value, Closure $fail) {
                    $host = strtolower(trim((string) $value));
                    $label = __('printer::attributes.print_agents.host');

                    if ($host === '') {
                        $fail(__('validation.required', ['attribute' => $label]));
                        return;
                    }

                    if (str_contains($host, '://') || str_contains($host, '/')) {
                        $fail(__('printer::print_agents.validation.host_without_scheme_or_path'));
                        return;
                    }

                    if (str_contains($host, ':')) {
                        $fail(__('printer::print_agents.validation.host_without_port'));
                        return;
                    }

                    $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
                    $isHostname = preg_match('/^(?=.{1,253}$)(?!-)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(?:\.(?!-)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i', $host) === 1;

                    if (!$isIp && !$isHostname) {
                        $fail(__('printer::print_agents.validation.host_valid_hostname_or_ip'));
                    }
                },
            ],
            "port" => "required|integer|min:1|max:65535",
        ];
    }

    /** @inheritDoc */
    protected function availableAttributes(): string
    {
        return "printer::attributes.print_agents";
    }
}
