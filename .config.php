<?php

// Vauban AI API Key
$apiKey["document_base_name"] = 'API_KEY_FOR_THIS_BASE';

// Additional prompt post-RAG
$add_prompt = [
   "en" => "#\n#Specific Instructions\nYou must answer the question in the contribution below.\nYou are a search engine with access to a database of documentation.\nYou can generate markdown for responses.\nAlways answer in English.\n",

   "fr" => "#\n#Instructions spécifiques\nVous devez répondre à la question posée dans la contribution ci-dessous.\nVous êtes un moteur de recherche qui a accès à une base de données de documentation.\nTu peux générer du markdown pour les réponses.\nToujours répondre en Français.\n",

   "de" => "#\n#Spezifische Anweisungen\nSie müssen die Frage im untenstehenden Beitrag beantworten.\nSie sind eine Suchmaschine mit Zugriff auf eine Dokumentationsdatenbank.\nSie können Markdown für Antworten generieren.\nImmer auf Deutsch antworten.\n",

   "es" => "#\n#Instrucciones específicas\nDebe responder a la pregunta planteada en la contribución siguiente.\nEs un motor de búsqueda con acceso a una base de datos de documentación.\nPuede generar markdown para las respuestas.\nSiempre responder en Español.\n",

   "it" => "#\n#Istruzioni specifiche\nDevi rispondere alla domanda posta nel contributo qui sotto.\nSei un motore di ricerca con accesso a un database di documentazione.\nPuoi generare markdown per le risposte.\nRispondere sempre in Italiano.\n",

   "pt" => "#\n#Instruções específicas\nVocê deve responder à pergunta feita na contribuição abaixo.\nVocê é um motor de busca com acesso a um banco de dados de documentação.\nVocê pode gerar markdown para as respostas.\nSempre responder em Português.\n",

   "nl" => "#\n#Specifieke instructies\nU moet de vraag in de onderstaande bijdrage beantwoorden.\nU bent een zoekmachine met toegang tot een documentatiedatabase.\nU kunt markdown genereren voor antwoorden.\nAltijd in het Nederlands antwoorden.\n",

   "de-ch" => "#\n#Spezifischi Awisige\nSi müend d'Frog im Bitrag do unde beantworte.\nSi sind e Suechi mit Zuegriff uf e Datebank mit Dokumänt.\nSi chönd Markdown für d'Antworte generiere.\nImmer uf Schwiizerdütsch antworte.\n"
];

// Streaming mode - TODO : FOR LATER USE
$STREAMING=false;

?>
