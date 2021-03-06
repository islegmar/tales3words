<?php
require_once(INTERNAL_ROOT_DIR . '/etl/ILoader.php');
require_once(INTERNAL_ROOT_DIR . '/db/NoRowSelectedException.php');

/**
 * In this implementation, the data is stored in the database using a a persistent
 * bean.
 */
class LoaderDBBean implements ILoader {
  // Bean created
  protected $beanName = null;
  // Optional : ignore if bean already exists using that field
  protected $fieldUnique = null;

	// ---------------------------------------------------------------- IExtractor
	/**
   * 'beanName' : The name of the bean to be persistent.
   */
  public function config($cfg) {
		$this->beanName = $cfg['beanName'];
    if ( array_key_exists('fieldUnique', $cfg) ) {
      $this->fieldUnique =  $cfg['fieldUnique'];     
    }
	}

  /**
   * Returns the next line splitted
   */
	public function load($data) {
    $obj = FactoryObject::newObject($this->beanName);

    // If we have ddefined an unique field, check if it exists before we insert
    if ( !is_null($this->fieldUnique)) {
      try {
        $obj->getByField($this->fieldUnique, $data[$this->fieldUnique]);
        // An existing record is found, skip
        // @TODO : an update should be possible
        return;
      } catch (NoRowSelectedException $e) {
        // No record found continue
      }
    }
    
    // OK, let's create that record!!!
    // @TODO : we should fill ONLY with the fields defined in the loader config
    $obj->fillBeanFromArray($data);
    $obj->createInDatabase();
	}
}
