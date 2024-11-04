<?php
class EmailTemplate
{
    public static function loadTemplate($templateName, array $variables = [])
    {
        $templatePath = __DIR__ . "/email_templates/{$templateName}.html";
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template non trouvé : $templatePath");
        }

        $templateContent = file_get_contents($templatePath);

        foreach ($variables as $key => $value) {
            $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $templateContent = str_replace("{{ $key }}", $escapedValue, $templateContent);
        }
        
        return $templateContent;
    }
}
