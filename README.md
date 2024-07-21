# SubmitMailer Plugin

This Joomla plugin sends an email notification when a new content or weblinks item gets submitted.

## Configuration

### Initial setup the plugin

- [Download the latest version of the plugin](https://github.com/zero-24/plg_content_submitmailer/releases/latest)
- Install the plugin using `Upload & Install`
- Enable the plugin `Content - SubmitMailer` from the plugin manager
- Setup the new Content Plugin `System -> Plugins -> Content - SubmitMailer`
- Setup the content and/or weblinks submission form and publish it to an menu

### Update Server

Please note that my update server only supports the latest version running the latest version of Joomla and atleast PHP 8.1.
Any other plugin version I may have added to the download section don't get updates using the update server.

## Issues / Pull Requests

You have found an Issue, have a question or you would like to suggest changes regarding this extension?
[Open an issue in this repo](https://github.com/zero-24/plg_content_submitmailer/issues/new) or submit a pull request with the proposed changes.

## Translations

You want to translate this extension to your own language? Check out my [Crowdin Page for my Extensions](https://joomla.crowdin.com/zero-24) for more details. Feel free to [open an issue here](https://github.com/zero-24/plg_content_submitmailer/issues/new) on any question that comes up.

## Joomla! Extensions Directory (JED)

This plugin can also been found in the Joomla! Extensions Directory: [SubmitMailer by zero24](https://extensions.joomla.org/extension/submitmailer/)

## Release steps

- `build/build.sh`
- `git commit -am 'prepare release SubmitMailer 3-6'`
- `git tag -s '3-6' -m 'SubmitMailer 3-6'`
- `git push origin --tags`
- create the release on GitHub
- `git push origin master`

## Crowdin

### Upload new strings

`crowdin upload sources`

### Download translations

`crowdin download --skip-untranslated-files --ignore-match`
