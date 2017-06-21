<?php
/**
* JoomBlog component for Joomla 3.x
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

if ( !defined('DS') ) define('DS', DIRECTORY_SEPARATOR);

class JbblogImagesTask extends JbBlogBaseController{

	private $postId = 0;
	private $post = false;

	private $fileFilter = '[\.jpg|\.jpeg|\.gif|\.png]+$';
	private $allowedMime = array( 'image/png', 'image/gif', 'image/jpeg', 'image/pjpeg' );

	private $mediaDir;
	private $mediaDirUrl;

	function __construct()
	{
		$this->mediaDir = JPATH_ROOT . DS . 'media' . DS . 'com_joomblog' . DS;
		$this->mediaDirUrl = JURI::root() . 'media/com_joomblog/';

		$this->subaction = JFactory::getApplication()->input->get('subaction', '');

		$db = JFactory::getDbo();

		$this->postId = JFactory::getApplication()->input->get('id', 0, 'INT');

		if ( $this->postId )
		{
			$db->setQuery("SELECT * FROM #__joomblog_posts WHERE id = ".$this->postId);
			$this->post = $db->loadObject();
		}

		if (
			empty($this->subaction) &&
			!JFactory::getUser()->get('isRoot')
			&& !JFactory::getUser()->id
		)
		{
			die('permissions error');
		}

	}

	function execute()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		switch ( $this->subaction )
		{

			case 'thumb':
				$filename = JFactory::getApplication()->input->get('file', '', 'RAW');
				$folderId = JFactory::getApplication()->input->get('folderid', 0);
				if ( $folderId != 'share' ) $folderId = (int)$folderId;		//secure

				$folder = $this->mediaDir. $folderId;

				if ( !preg_match('/'. $this->fileFilter .'/', $filename) || !JFolder::exists($folder) || !JFile::exists($folder.DS.$filename) )
					die('permissions error');

				$this->showThumb($folder.DS.$filename);
			break;

			case 'upload':

				$result = array();

				$folder = JFactory::getApplication()->input->get('folder', JText::_('COM_JOOMBLOG_IMAGES_MY'), 'RAW');

				$folderMediaPath = $this->getFolderByName($folder);

				if ( $folderMediaPath )
				{
					foreach ($_FILES['files']['name'] as $k => $filename)
					{
						if ( in_array($_FILES['files']['type'][$k], $this->allowedMime) )
						{
							$filename = preg_replace('/[^a-z0-9-_\. ]+/is', '_', $filename);

							$uploadResult = JFile::upload( $_FILES['files']['tmp_name'][$k], $folderMediaPath.$filename );

							if ( $uploadResult )
							{
								$result[] = array(
									'name' => $filename,
									'size' => $_FILES['files']['size'][$k],
									'type' => $_FILES['files']['type'][$k],
									'deleteUrl' => JURI::root() . 'index.php?option=com_joomblog&task=images&subaction=delete&file='.urlencode($filename).'&folder='.$folder.'&id='.$this->post->id,
								);
							}
							else
							{
								print_r(JError::getErrors());
							}
						}
					}
				}

				JFactory::getDocument()->setMimeEncoding( 'application/json' );
				echo json_encode( array('files'=> $result) );
				JFactory::getApplication()->close();
			break;

			case 'delete':

				$folder = JFactory::getApplication()->input->get('folder', JText::_('COM_JOOMBLOG_IMAGES_MY'), 'RAW');
				$filename = JFactory::getApplication()->input->get('file', '', 'RAW');

				$folderMediaPath = $this->getFolderByName($folder);

				if ( !empty($filename) && $folderMediaPath )
				{
					JFile::delete($folderMediaPath.$filename);
				}

				die('1');
			break;

			// Show images List
			default:
				$result = array();

				$myFolder = JFolder::files( $this->mediaDir . JFactory::getUser()->id, $this->fileFilter );
				if ( !empty($myFolder) )
				{
					foreach ($myFolder as $file)
					{
						$result[] = array(
							'file' => $file,
							'image' => $this->mediaDirUrl . JFactory::getUser()->id . '/' . $file,
							'thumb' => JURI::root() . 'index.php?option=com_joomblog&task=images&folderid='.JFactory::getUser()->id.'&subaction=thumb&file='.urlencode($file),
							'folder' => JText::_('COM_JOOMBLOG_IMAGES_MY')
						);
					}
				}
				else
				{
					$result[] = array(
						'file' => '',
						'image' => '',
						'thumb' => '',
						'folder' => JText::_('COM_JOOMBLOG_IMAGES_MY')
					);
				}

				$shareFolder = JFolder::files( $this->mediaDir . 'share', $this->fileFilter );
				if ( !empty($shareFolder) )
				{
					foreach ($shareFolder as $file)
					{
						$result[] = array(
							'file' => $file,
							'image' => $this->mediaDirUrl . 'share/' . $file,
							'thumb' => JURI::root() . 'index.php?option=com_joomblog&task=images&folderid=share&subaction=thumb&file='.urlencode($file),
							'folder' => JText::_('COM_JOOMBLOG_IMAGES_SHARE')
						);
					}
				}
				else
				{
					$result[] = array(
						'file' => '',
						'image' => '',
						'thumb' => '',
						'folder' => JText::_('COM_JOOMBLOG_IMAGES_SHARE')
					);
				}

				if ( JFactory::getUser()->get('isRoot') && !empty($this->post->created_by) && $this->post->created_by != JFactory::getUser()->id )
				{
					$bloggerFolder = JFolder::files( $this->mediaDir . $this->post->created_by, $this->fileFilter );
					if ( !empty($bloggerFolder) )
					{
						foreach ($bloggerFolder as $file)
						{
							$result[] = array(
								'image' => $this->mediaDirUrl . $this->post->created_by . '/' . $file,
								'thumb' => JURI::root() . 'index.php?option=com_joomblog&task=images&folderid='.$this->post->created_by.'&subaction=thumb&file='.urlencode($file),
								'folder' => JText::_('COM_JOOMBLOG_IMAGES_USER')
							);
						}
					}
					else
					{
						$result[] = array(
							'image' => '',
							'thumb' => '',
							'folder' => JText::_('COM_JOOMBLOG_IMAGES_USER')
						);
					}
				}

				JFactory::getDocument()->setMimeEncoding( 'application/json' );
				echo json_encode($result);
				JFactory::getApplication()->close();

			break;
		}
	}

	function getFolderByName( $folder )
	{
		// get Folder Media Path by Folder Name (and check permissions)
		switch ($folder)
		{
			case JText::_('COM_JOOMBLOG_IMAGES_SHARE'):
				return $this->mediaDir . 'share/';
			break;

			case JText::_('COM_JOOMBLOG_IMAGES_USER'):
				if ( JFactory::getUser()->get('isRoot') && $this->post->created_by != JFactory::getUser()->id )
					return $this->mediaDir . $this->post->created_by.DS;
				else
					return false;
			break;

			default:
				return $this->mediaDir . JFactory::getUser()->id . DS;
			break;
		}
	}

	// generating thumbnail and show it
	function showThumb($file, $th_width = 110, $th_height = 110)
	{
		require_once( JPATH_ROOT . DS .'components'. DS . 'com_joomblog'. DS .'assets'. DS .'ckeditor'. DS .'itb.php');
		$Itb = new Image_Toolbox($file);
		$Itb->newOutputSize((int)$th_width, (int)$th_height, 4);
		$Itb->output(false, 70);

		exit;
	}
}

