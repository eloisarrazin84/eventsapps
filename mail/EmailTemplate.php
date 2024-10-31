<?php

class EmailTemplate
{
    public static function loadTemplate($templateName, $variables = [])
    {
        $templatePath = __DIR__ . "/email_templates/$templateName.html";
        
        if (file_exists($templatePath)) {
            $templateContent = file_get_contents($templatePath);
            
            // Remplacement des variables dans le template
            foreach ($variables as $key => $value) {
                $templateContent = str_replace("{{ $key }}", htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $templateContent);
            }
            
            return $templateContent;
        } else {
            throw new Exception("Template non trouvé : $templatePath");
        }
    }
}
