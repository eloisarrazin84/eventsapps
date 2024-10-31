<?php

class EmailTemplate
{
    /**
     * Charge un template d'email et remplace les variables.
     * 
     * @param string $templateName Nom du fichier de template sans l'extension.
     * @param array $variables Tableau associatif des variables à remplacer dans le template.
     * @return string Contenu du template avec les variables remplacées.
     * @throws Exception Si le template est introuvable.
     */
    public static function loadTemplate($templateName, array $variables = [])
    {
        // Chemin vers le template
        $templatePath = __DIR__ . "/email_templates/$templateName.html";
        
        // Vérification de l'existence du fichier template
        if (!file_exists($templatePath)) {
            throw new Exception("Template non trouvé : $templatePath");
        }

        // Chargement du contenu du template
        $templateContent = file_get_contents($templatePath);

        // Remplacement des variables dans le template
        foreach ($variables as $key => $value) {
            // Encodage des caractères spéciaux et remplacement
            $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $templateContent = str_replace("{{ $key }}", $escapedValue, $templateContent);
        }
        
        return $templateContent;
    }
}
