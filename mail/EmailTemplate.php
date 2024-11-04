<?php

class EmailTemplate
{
    public static function loadTemplate($templateName, array $variables = [])
    {
        // Chemin vers le template
        $templatePath = __DIR__ . "/email_templates/$templateName";

        // Vérification de l'existence du fichier template
        if (!file_exists($templatePath)) {
            throw new Exception("Template non trouvé : $templatePath");
        }

        // Chargement du contenu du template
        $templateContent = file_get_contents($templatePath);

        // Remplacement des variables dans le template
        foreach ($variables as $key => $value) {
            $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $templateContent = str_replace("{{ $key }}", $escapedValue, $templateContent);
        }

        return $templateContent;
    }
}
