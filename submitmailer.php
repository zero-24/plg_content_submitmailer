<?php
/**
 * SubmitMailer Plugin
 *
 * @copyright  Copyright (C) 2016 Tobias Zulauf All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

class plgContentSubmitMailer extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 * @since  3-6
	 */
	protected $app;

	/**
	* Load the language file on instantiation.
	*
	* @var    boolean
	* @since  3-1
	*/
	protected $autoloadLanguage = true;

	/**
	 * After save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param   string  $context  The context of the content passed to the plugin
	 * @param   object  $article  A JTableContent object
	 * @param   boolean $isNew    If the content has just been created
	 *
	 * @since   3-1
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		// Check this is a new article.
		if (!$isNew)
		{
			return true;
		}

		// Check we are handling the correct context.
		if ($context != 'com_content.article' && $context != 'com_content.form' && $context != 'com_weblinks.form')
		{
			return true;
		}

		// Set the contex if we are there and enabled
		if ($context == 'com_content.article' && $this->params->get('article_backend', 0) == 1)
		{
			$this->context = $context;
		}
		elseif ($context == 'com_content.form' && $this->params->get('article', 0) == 1)
		{
			$this->context = $context;
		}
		elseif ($context == 'com_weblinks.form' && $this->params->get('weblink', 0) == 1)
		{
			$this->context = $context;
		}
		else
		{
			return true;
		}

		// Initialise global variables
		$this->user    = Factory::getUser();
		$this->db      = Factory::getDbo();
		$params        = $this->params;
		$this->app     = Factory::getApplication();
		$this->article = $article;
		$this->sub     = '';
		$this->body    = '';

		if ($this->context == 'com_content.article' || $this->context == 'com_content.form')
		{
			$this->sub  = $params->get('subject_article', '');
			$this->body = $params->get('body_article', '');
		}
		elseif ($this->context == 'com_weblinks.form')
		{
			$this->sub  = $params->get('subject_weblink', '');
			$this->body = $params->get('body_weblink', '');
		}

		$this->setEmailContent();

		// Catch email to users there have systememail enabled
		if ((int) $params->get('mailto_admins') == 1)
		{
			// Catch the emails from the DB
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('email'))
				->from($this->db->quoteName('#__users'))
				->where($this->db->quoteName('sendEmail') . " = " . 1);
			$this->db->setQuery($query);
			$emails = (array) $this->db->loadColumn();

			$this->sendMail($emails);
			unset($emails);
		}

		// Catch email to custom addresses
		if ((int) $params->get('mailto_custom') == 1)
		{
			// Catch the emails for the field
			$customEmails = $params->get('custom_emails');

			// Diese Zeile ist für B/C
			$customEmails = $customEmails . $params->get('custom_email_article');
			$emails       = explode(';', $customEmails);

			$this->sendMail($emails);
			unset($emails);
		}
	}

	/**
	 * This method set all variables for the Email Content like sub, body etc.
	 *
	 * @retrun  void
	 *
	 * @since   3-3
	 */
	private function setEmailContent()
	{
		if ($this->context == 'com_content.article' or $this->context == 'com_content.form')
		{
			// Site url for the new itm
			$siteURL = Uri::root() . 'index.php?option=com_content&view=article&id=' . $this->article->id;

			// Set body to default if it is clear
			if ($this->body == '')
			{
				$this->body = Text::sprintf('PLG_CONTENT_SUBMIT_MAILER_CONTENT_BODY_DEFAULT');
			}

			// Set sub to default if it is clear
			if ($this->sub == '')
			{
				$this->sub = Text::sprintf('PLG_CONTENT_SUBMIT_MAILER_CONTENT_SUBJECT_DEFAULT');
			}
			// Gets category ID and name with the Article ID
			$category_id = $this->db->getQuery(true)
				->select($this->db->quoteName('catid'))
				->from($this->db->quoteName('#__content'))
				->where($this->db->quoteName('id') . " = " . $this->article->id);
			$this->db->setQuery($category_id);
			$category_id = $this->db->loadResult();

			$category = $this->db->getQuery(true)
				->select($this->db->quoteName('title'))
				->from($this->db->quoteName('#__categories'))
				->where($this->db->quoteName('id') . " = " . $category_id);
			$this->db->setQuery($category);
			$category = $this->db->loadResult();

			// gets introtext with the Article ID
			$introtext = $this->db->getQuery(true)
				->select($this->db->quoteName('introtext'))
				->from($this->db->quoteName('#__content'))
				->where($this->db->quoteName('id') . " = " . $this->article->id);
			$this->db->setQuery($introtext);
			$introtext = $this->db->loadResult();
			$introtext = str_replace('<br>', '<br />', $introtext);

			// gets fulltext with the Article ID
			$fulltext = $this->db->getQuery(true)
				->select($this->db->quoteName('fulltext'))
				->from($this->db->quoteName('#__content'))
				->where($this->db->quoteName('id') . " = " . $this->article->id);
			$this->db->setQuery($fulltext);
			$fulltext = $this->db->loadResult();
			$fulltext = str_replace('<br>', '<br />', $fulltext);

			// gets title with the Article ID
			$title = $this->db->getQuery(true)
				->select($this->db->quoteName('title'))
				->from($this->db->quoteName('#__content'))
				->where($this->db->quoteName('id') . " = " . $this->article->id);
			$this->db->setQuery($title);
			$title = $this->db->loadResult();

			// define article introtext and fulltext
			if ($fulltext == '')
			{
				$fulltext = $introtext;
			}
			else
			{
				$fulltext = $introtext. '<br>' . $fulltext;
			}

			// prepare email body
			$this->body = str_replace('{username}', $this->user->get('username'), $this->body);
			$this->body = str_replace('{category}', $category, $this->body);
			$this->body = str_replace('{title}', $title, $this->body);
			$this->body = str_replace('{introtext}', $introtext, $this->body);
			$this->body = str_replace('{fulltext}', $fulltext, $this->body);
			$this->body = str_replace('{link}', $siteURL, $this->body);
		}

		if ($this->context == 'com_weblinks.form')
		{
			// Backend url for the new weblink
			$siteURL = Uri::root() . 'administrator/index.php?option=com_weblinks&view=weblink&layout=edit&id=' . $this->article->id;

			// Set body to default if it is clear
			if ($this->body == '')
			{
				$this->body = Text::sprintf('PLG_CONTENT_SUBMIT_MAILER_WEBLINK_BODY_DEFAULT');
			}

			// Set sub to default if it is clear
			if ($this->sub == '')
			{
				$this->sub = Text::sprintf('PLG_CONTENT_SUBMIT_MAILER_WEBLINK_SUBJECT_DEFAULT');
			}

			// Gets category ID and name with the Weblink ID
			$category_id = $this->db->getQuery(true)
				->select($this->db->quoteName('catid'))
				->from($this->db->quoteName('#__weblinks'))
				->where($this->db->quoteName('id') . " = " . $this->article->id);
			$this->db->setQuery($category_id);
			$category_id = $this->db->loadResult();

			$category = $this->db->getQuery(true)
				->select($this->db->quoteName('title'))
				->from($this->db->quoteName('#__categories'))
				->where($this->db->quoteName('id') . " = " . $category_id);
			$this->db->setQuery($category);
			$category = $this->db->loadResult();

			// Gets title with the Weblink ID
			$title = $this->db->getQuery(true)
				->select($this->db->quoteName('title'))
				->from($this->db->quoteName('#__weblinks'))
				->where($this->db->quoteName('id') . " = " . $this->article->id);
			$this->db->setQuery($title);
			$title = $this->db->loadResult();

			// Gets weblink link with the ID
			$link = $this->db->getQuery(true)
				->select($this->db->quoteName('url'))
				->from($this->db->quoteName('#__weblinks'))
				->where($this->db->quoteName('id') . " = " . $this->article->id);
			$this->db->setQuery($link);
			$link = $this->db->loadResult();

			// prepare email body
			$this->body = str_replace('{username}', $this->user->get('username'), $this->body);
			$this->body = str_replace('{category}', $category, $this->body);
			$this->body = str_replace('{title}', $title, $this->body);
			$this->body = str_replace('{weblink}', $link, $this->body);
			$this->body = str_replace('{link}', $siteURL, $this->body);
		}

		// Prepare email subject
		$this->sub = str_replace('{url}', Uri::base(), $this->sub);
	}
	/**
	 * This method set all vars for the Email Content like sub, body etc.
	 *
	 * @param   array  $emails  An Array of email recipient
	 *
	 * @retrun  void
	 *
	 * @since   3-3
	 */
	private function sendMail($emails)
	{
		// Die Mail and die Empfänger senden
		foreach ($emails AS $email)
		{
			$email = trim($email);

			if ($email == '')
			{
				return;
			}

			$mail = Factory::getMailer();
			$mail->IsHTML(true);
			$mail->setSender(array($this->app->get('mailfrom'), $this->app->get('fromname')));
			$mail->addRecipient($email);
			$mail->setSubject($this->sub);
			$mail->setBody($this->body);
			$send_mail = $mail->Send();

			// Sollte beim senden der Email ein Fehler passieren sende via PHPMAIL
			if ($send_mail == false)
			{
				// Fehler Email an den Admin
				$error_sub = Text::sprintf('PLG_CONTENT_SUBMIT_MAILER_ERROR_SUB', Uri::base());

				if ($this->context == 'com_content.article' or $this->context == 'com_content.form')
				{
					$error_body = Text::sprintf('PLG_CONTENT_SUBMIT_MAILER_CONTENT_ERROR_BODY', Uri::base());
				}

				if ($this->context == 'com_weblinks.form')
				{
					$error_body = Text::sprintf('PLG_CONTENT_SUBMIT_MAILER_WEBLINK_ERROR_BODY', Uri::base());
				}

				mail($this->app->get('mailfrom'), $error_sub, $error_body, $this->app->get('fromname'));

				return;
			}
		}
	}
}

