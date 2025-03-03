# Smart Search System Setup Guide

This document provides step-by-step instructions for setting up and customizing your Smart Search system after cloning the repository.

By default, sample configurations are provided with "My Smart Company" as an example business which would be a brain implant business internal search engine.
However, you can use this interface for internal or Internet-facing use-cases without any limitations.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Installation](#installation)
3. [Configuration Files](#configuration-files)
   - [.config.php](#configphp)
   - [www/config.json](#wwwconfigjson)
   - [www/questions.json](#wwwquestionsjson)
4. [Customizing Language Files](#customizing-language-files)
5. [Prompt Engineering](#prompt-engineering)
6. [Updating Favicon](#updating-favicon)
7. [Testing Your Installation](#testing-your-installation)
8. [Troubleshooting](#troubleshooting)

## Prerequisites

Before starting the installation, ensure you have:
- Web server (HTTPS only) with PHP 7.4+ support (PHP required only in the "api/" folder)
- API key(s) for Vauban AI (one key for each document base)
- Git installed on your system
- Basic understanding of JSON and PHP configuration

If you want to use the editor and need to upload large documents, you will have to raise some limits in your php.ini :
upload_max_filesize = 47M
post_max_size = 48M

Your webserver should also allow large POST.
For example, on Nginx, this is done with setting client_max_body_size 48M;

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Vauban-Cloud/smartsearch.git
   cd smartsearch
   ```

2. Set proper permissions:
   ```bash
   chmod 750 ./www
   chmod 750 ./www/api
   chmod 750 ./www/assets
   chmod 750 ./www/icons
   chmod 660 ./.config.php
   chgrp -R <your_PHP_user> ./www ./.config.php
   ```

3. Configure your web server to point to the `www` directory.

## Configuration Files

You'll need to modify three configuration files to customize your installation:

### .config.php

This file contains API keys and language-specific prompts, it is not in a browsable directory.
You can change the location of this file to better suit your needs by editing the first line of all PHP scripts in the www/api/ folder.

1. Open `.config.php` in your editor.
2. Replace `'API_KEY_FOR_THIS_BASE'` with your actual Vauban AI API key for each document base.
3. Customize the language-specific prompts in the `$add_prompt` array if needed.
4. Keep the `$STREAMING` variable as `false` for now (future functionality).

Example configuration:

```php
<?php
// Vauban AI API Key
$APIKEY["my_company_docs"] = 'your-actual-api-key-12345';

// Access-Control-Allow-Origin
// FOR PRODUCTION, PUT THE URL OF THE SITE OR PHP SCRIPTS ROOT HERE
// ie: https://smartsearch.example.com
$ALLOWORIGIN = "*";

// Editor's authentication credentials
//
// LEAVE EMPTY TO DISABLE EDITOR

// best practice: change me...
$USERNAME = "admin";

// UPDATE THIS FIELD!
// echo -n "Your Best Password..." | openssl dgst -sha3-512 | cut -d ' ' -f 2
$HASHEDPASS = "";

// Additional prompt post-RAG
$ADDPROMPT = [
   "en" => "#\n#Specific Instructions\nYou must answer the question in the contribution below.\nYou are a search engine with access to a database of documentation.\nYou can generate markdown for responses.\nAlways answer in English.\n",
   // Other languages...
];
// Streaming mode (FOR LATER USE)
$STREAMING=false;
?>
```

### www/config.json

This file controls the user interface configuration.

1. Open `www/config.json` in your editor.
2. Customize the following fields:
   - `title`: Your company name and application title
   - `defaultLang`: Default language code from "en", "fr", "es", "it", "nl", "pt", "de", "de-ch"
   - `colors`: Customize the color scheme
   - `logoPath`: Path to your company logo
   - `url`: The URL users will be redirected to when they click on the logo

Example configuration:

```json
{
  "title": "My Smart Company - Smart Search Engine",
  "defaultLang": "en",
  "colors": {
    "primary": "#7b8dc6",
    "secondary": "#7b8dc6",
    "accent": "#f4a74b",
    "dark": "#3c3c3c"
  },
  "logoPath": "logo-smart.png",
  "url": "https://example.com/",
  "filesList": "/api/list.php",
  "searchUrl": "/api/search.php",
  "storageUrl": "/files/",
  "editorRoot": "/",
  "suggestions": [
  {
    "text": "short-question1",
    "question": "long-question1",
    "icon": "fa-box"
  },
  {
    "text": "short-question2",
    "question": "long-question2",
    "icon": "fa-book"
  },
  {
    "text": "short-question3",
    "question": "long-question3",
    "icon": "fa-brain"
  }
  ]
}
```

You can find icons on the [Icon Explorer website](https://iconexplorer.app/icons/fontawesome-v6?selected). Feel free to use any icon whose name starts with "fas".

> **Note**: The `"text"` and `"question"` fields in the suggestions array are used as keys that will be replaced with language-specific content from the `questions.json` file.

### www/questions.json

This file contains language-specific questions displayed as suggestions to users.

1. The file should already be populated with sample questions in various languages.
2. Replace the predefined questions with your company-specific questions.
3. Ensure all language codes match those in the `.config.php` file.

## Customizing Language Files

You need to modify eight markdown files, one for each supported language:

- `www/intro_en.md` (English)
- `www/intro_fr.md` (French)
- `www/intro_de.md` (German)
- `www/intro_es.md` (Spanish)
- `www/intro_it.md` (Italian)
- `www/intro_pt.md` (Portuguese)
- `www/intro_nl.md` (Dutch)
- `www/intro_de-ch.md` (Swiss German)

For each file:

1. Open the file in your editor.
2. Customize the content while keeping the following elements:
   - `<search-input/>` (mandatory): This tag places the search input box on the front page.
   - `<search-suggestions/>` (optional): This tag displays search suggestions from questions.json.
3. Maintain the overall structure, but personalize the content to reflect your company's documentation and focus areas.

Example for `www/intro_en.md`:

```markdown
### Access Your Company's Proprietary Neural Interface Researchi

###### Search our comprehensive knowledge base of research papers, technical specifications, and patent information using AI-powered assistance.

<search-input/>
<search-suggestions/>

Our secure document repository contains over 500 internal research papers, 2,000 technical specifications, and 300 patent applications related to our neural interface technology.

This platform provides all team members with instant access to our complete research portfolio, from early conceptual designs to the latest clinical trial results.

Understanding our existing research is crucial for advancing our neural interface technology into the next generation of medical devices.

**<center>Discover insights, track progress across research teams, and find collaboration opportunities.</center>**
```


## Prompt Engineering

The `$add_prompt` array in `.config.php` allows you to customize how the AI responds to user queries. Effective prompt engineering can significantly enhance search quality and user experience.

### Key Principles for Customizing Prompts

When modifying the language-specific prompts in `$add_prompt`, consider these best practices:

1. **Define the AI's role clearly.** The default setting positions the AI as "a search engine with access to a database of documentation." You may customize this role to better match your use case, such as "a neural technology expert" or "a research assistant specializing in brain-computer interfaces."

2. **Establish response parameters.** Specify output format, tone, and style requirements. For example, add instructions like "Use technical language appropriate for researchers" or "Include citations to relevant documents when possible."

3. **Set content boundaries.** If certain topics should be handled differently, include specific guidance such as "For questions about clinical applications, emphasize safety considerations" or "When discussing proprietary technology, focus on general capabilities rather than implementation details."

4. **Maintain language consistency.** Ensure each language version conveys the same instructions while being culturally appropriate for speakers of that language.

### Example of Enhanced $add_prompt Configuration

```php
$add_prompt = [
   "en" => "#\n#Specific Instructions\nYou are a neural interface research assistant with expertise in brain-computer interfaces.\nRespond with technical accuracy while making complex concepts accessible.\nInclude references to specific documents when relevant.\nOrganize long responses with markdown headings.\nDirect implementation questions to technical documentation.\nAlways answer in English.\n",
   // Other languages follow similar pattern with translated content
];
```

Remember that prompt engineering is iterative—review search results periodically and refine your prompts based on user feedback and search performance.


## Updating Favicon

A favicon matching your logo/theme enhances your Smart Search's appearance by displaying your company logo in browser tabs and bookmarks. Follow these steps to implement your favicon:

1. Convert your logo image to the appropriate favicon formats:
   - Navigate to https://favicon.io/favicon-converter/
   - Upload your PNG logo image (minimum 512x512 pixels, square format)
   - Download the resulting ZIP file containing the converted favicon files

2. The ZIP file should contain the following essential files:
   - android-chrome-192x192.png
   - android-chrome-512x512.png
   - apple-touch-icon.png
   - favicon-16x16.png
   - favicon-32x32.png
   - favicon.ico
   - site.webmanifest

3. Extract and organize the files:
   - Unzip the downloaded file
   - Place favicon.ico file in your Smart Search system's `www/` directory
   - Move all PNG files to the `www/icons/` folder
   - Ignore the site.webmanifest file, as it already exists

4. No further configuration is necessary. The system will automatically detect and use these files to display your favicon across different browsers and devices.


## Testing Your Installation

After completing your configuration:

1. Start your web server if it's not already running.
2. Access your Smart Search system through the configured URL.
3. Test each language option to ensure the correct content displays.
4. Verify that the search functionality works by entering test queries.
5. Confirm that suggestions appear and function correctly when clicked.
6. Well, looks like this is now ready for Production (!TGIF).


## Troubleshooting

If you encounter issues:

- **API Key Errors**: Verify your API keys in `.config.php` are correct and properly formatted.
- **Missing Content**: Ensure all language files are present and contain the required tags.
- **Display Problems**: Check that your web server has the correct permissions for all files.
- **Search Failures**: Confirm your document base is properly configured in the Vauban AI system.

For additional support, refer to the repository documentation or contact our helpdesk through our website at https://vauban.cloud/

