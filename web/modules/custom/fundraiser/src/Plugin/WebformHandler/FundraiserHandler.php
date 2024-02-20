<?php

namespace Drupal\fundraiser\Plugin\WebformHandler;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Webform example handler.
 *
 * @WebformHandler(
 *   id = "example",
 *   label = @Translation("FundraiserHandler"),
 *   category = @Translation("FundraiserHandler"),
 *   description = @Translation("FundraiserHandler of a webform submission handler."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class FundraiserHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    if ($webform_submission->getElementData('fundraiser')) {
      $fid = $webform_submission->getElementData('fundraiser_page_image');
      $file = File::load($fid);
      $file_name = $file->getFilename();

      /** @var \Drupal\Core\File\FileSystemInterface $file_system */
      $file_system = \Drupal::service('file_system');

      $image_path = $file->getFileUri();

      $destination = 'public://fundraiser/' . $fid . '-' . $file_name;
      $destination_dir = dirname($destination);
      $file_system->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      $file_system->copy($image_path, $destination, FileSystemInterface::EXISTS_REPLACE);

      $file = File::create([
        'uid' => 1,
        'filename' => $fid . '-' . $file_name,
        'uri' => $destination,
        'status' => 1,
      ]);
      $file->save();

      $entity_type_manager = \Drupal::entityTypeManager();
      $media = $entity_type_manager->getStorage('media')->create([
        'bundle' => 'image',
        'name' => $fid . '-' . $file_name,
        'field_media_image' => $file,
      ]);
      $media->save();

      $node = Node::create([
        'type' => 'fundraiser',
        'title' => $webform_submission->getElementData('fundraiser_page_title'),
        'body' => [
          'value' => $webform_submission->getElementData('fundraiser_page_description'),
          'format' => 'full_html'
        ],
        'field_image'=> $file
      ]);

      $node->save();
    }
  }
}
