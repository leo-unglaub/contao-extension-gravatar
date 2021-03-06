<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Unglaub 2012
 * @author     Leo Unglaub <leo@leo-unglaub.net>
 * @package    lib_gravatar
 * @license    LGPL
 * @filesource
 */


/**
 * Class Gravatar
 * Provide methods to fetch gravatars and cache them
 */
class Gravatar extends System
{
	/**
	 * Return the path for an image witch can be used directly in an <img> tag
	 * 
	 * @param string $strEmail
	 * @param int $intSize
	 * @return string
	 */
	public function getRemotePath($strEmail, $intSize='')
	{
		$strPath = 'http://www.gravatar.com/avatar/' . $this->generateHash($strEmail);

		// add the size parameter if the user has given it
		if ($intSize != '' && is_int($intSize))
		{
			$strPath .= '?s=' . $intSize;
		}

		return $strPath;
	}


	/**
	 * Return the local cached path for an image witch can be used directly in an <img> tag
	 * 
	 * @param string $strEmail
	 * @param int $intSize
	 * @return string
	 */
	public function getLocalPath($strEmail, $intSize='')
	{
		$strRemotePath = $this->getRemotePath($strEmail, $intSize);
		$strLocalPath = 'system/html/gravatar_' . md5($strRemotePath) . '.jpg';

		// check if the cached file exists
		if (file_exists(TL_ROOT . '/' . $strLocalPath))
		{
			return $strLocalPath;
		}

		// there is no local cached image, so we get it an store it in the cache
		$objRequestImage = new Request();
		$objRequestImage->method = 'GET';
		$objRequestImage->send($strRemotePath);

		// if there are no errors during the request, write the cache file
		if ($objRequestImage->hasError() != true && $objRequestImage->code == 200)
		{
			// store the image in the local cache
			$objCachedImage = new File($strLocalPath);
			$objCachedImage->write($objRequestImage->response);
			$objCachedImage->close();

			// return the path to the new local file
			return $strLocalPath;
		}

		// Fallback if there was an error writing/requesting the cached file
		return $strRemotePath;
	}


	/**
	 * Return a html <img> tag containing the remote image
	 * 
	 * Note: There is no way of determinating the with and height of the remote
	 *       image. So the <img> tag is not xHTML valid.
	 * 
	 * @param string $strEmail
	 * @param int $intSize
	 * @return string
	 */
	public function getRemoteImageTag($strEmail, $intSize='')
	{
		return '<img src="' . $this->getRemoteImage($strEmail, $intSize) . '" />';
	}


	/**
	 * Return a html <img> tag containing the local image
	 * 
	 * @param string $strEmail
	 * @param int $intSize
	 * @return string
	 */
	public function getLocalImageTag($strEmail, $intSize='')
	{
		$strImage = $this->getLocalPath($strEmail, $intSize);
		$objLocalImage = new File($strImage);

		return '<img src="' . $strImage . '" width="' . $objLocalImage->width . '" height="' . $objLocalImage->height . '" />';
	}


	/**
	 * Return the gravatar hash based on the email address
	 * 
	 * @param string $strEmail
	 * @return string
	 */
	protected function generateHash($strEmail)
	{
		$strEmail = trim($strEmail);
		$strEmail = strtolower($strEmail);
		$strHash = md5($strEmail);

		return $strHash;
	}
}

?>