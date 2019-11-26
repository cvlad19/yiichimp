<?php
/**
 * @copyright Copyright (c) 2017 Usha Singhai Neo Informatique Pvt. Ltd
 * @license https://github.com/eduardo-g-silva/yiichimp/blob/master/LICENSE.md
 */
namespace usni\library\modules\users\models;

use usni\library\db\ActiveRecord;
use usni\library\utils\FileUploadUtil;
use usni\UsniAdaptor;
use usni\library\validators\EmailValidator;
use usni\library\validators\FileSizeValidator;
use usni\library\utils\ArrayUtil;
/**
 * This is the model class for table "person. The followings are the available model relations.
 * 
 * @package usni\library\modules\users\models
 */
class Person extends ActiveRecord
{
    /**
     * Upload File Instance.
     * @var string
     */
    public $savedImage;
    
    /**
     * Upload File Instance.
     * @var string
     */
    public $uploadInstance;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        if($this->checkIfExtendedConfigExists())
        {
            $configInstance = $this->getExtendedConfigClassInstance();
            $labels         = $configInstance->attributeLabels();
        }
        else
        {
            $labels = [
                        'id'                => UsniAdaptor::t('application', 'Id'),
                        'dancing_role'      => UsniAdaptor::t('users','Dancing Role'),
                        'firstname'         => UsniAdaptor::t('users','Your First Name'),
                        'lastname'          => UsniAdaptor::t('users','Your Last Name'),
                        'couple'            => UsniAdaptor::t('users','Registering as a Couple?'),
                        'partner_firstname' => UsniAdaptor::t('users','Partner First Name'),
                        'partner_lastname' => UsniAdaptor::t('users','Partner Last Name'),
                        'mobilephone'       => UsniAdaptor::t('users','Mobile'),
                        'email'             => UsniAdaptor::t('users','Email'),
                        'fullName'          => UsniAdaptor::t('users','Full Name'),
                        'profile_image'     => UsniAdaptor::t('users','Profile Image')
                      ];
        }
        return parent::getTranslatedAttributeLabels($labels);
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        if($this->checkIfExtendedConfigExists())
        {
            $configInstance = $this->getExtendedConfigClassInstance();
            $scenarios      = $configInstance->scenarios();
            return $scenarios;
        }
        else
        {
            $scenarios                  = parent::scenarios();
            $commonAttributes           = ['email', 'firstname', 'lastname', 'couple', 'dancing_role','partner_firstname','partner_lastname', 'mobilephone'];
            $scenarios['create']        = $scenarios['update'] = ArrayUtil::merge($commonAttributes, ['profile_image']);
            $scenarios['registration']  = $scenarios['editprofile'] = ['firstname', 'lastname', 'email', 'couple', 'dancing_role', 'partner_firstname', 'parter_lastname', 'mobilephone'];
            $scenarios['supercreate']   = $commonAttributes;
            $scenarios['bulkedit']      = ['firstname', 'lastname', 'couple','dancing_role', 'partner_firstname', 'partner_lastname', 'mobilephone'];
            $scenarios['deleteimage']   = ['profile_image'];
            return $scenarios;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        if($this->checkIfExtendedConfigExists())
        {
            $configInstance = $this->getExtendedConfigClassInstance();
            $rules          = $configInstance->rules();
            return $rules;
        }
        else
        {
            return array(
                //Person rules
                [['firstname', 'lastname'],         'required'],
                [['firstname', 'lastname'],         'match', 'pattern' => '/^[A-Z._]+$/i'],
                [['firstname', 'lastname'],         'string', 'max' => 32],
                ['email',                           'required'],
                ['email',                           'unique', 'targetClass' => Person::className(), 'on' => ['create', 'registration']],
                ['email',                           'unique', 'targetClass' => Person::className(), 'on' => ['update', 'editprofile'], 
                                                    'filter' => ['!=', 'id', $this->id]],
                ['email',                           EmailValidator::className()],
                [['couple'],                        'required'],
                [['couple'],                        'boolean'],
                [['dancing_role'],                  'required'],
                [['dancing_role'],                  'string'],
                [['partner_firstname'],               'string'],
                [['partner_lastname'],               'string'],
                [['mobilephone'],                   'number'],
                [['profile_image'],                 'file'],
                ['profile_image',                   FileSizeValidator::className()],
                [['profile_image', 'uploadInstance'],       'image', 'skipOnEmpty' => true, 'extensions' => 'jpg, png, gif, jpeg'],
                [['firstname', 'lastname', 'mobilephone', 'couple', 'dancing_role'],  'safe'],
            );
        }
    }

    /**
     * Gets profile image.
     * @param array $htmlOptions
     * @return mixed
     */
    public function getProfileImage($htmlOptions = array())
    {
        return FileUploadUtil::getThumbnailImage($this, 'profile_image', $htmlOptions);
    }

    /**
     * Get address for the person.
     * @return \Address
     */
    public function getAddress()
    {
        return $this->hasOne(Address::className(), ['relatedmodel_id' => 'id'])
                    ->where('relatedmodel = :rm AND type = :type', [':rm' => 'Person', ':type' => Address::TYPE_DEFAULT]);
    }
    
    /**
     * @inheritdoc
     */
    public static function getLabel($n = 1)
    {
        return UsniAdaptor::t('users', 'Person');
    }
    
    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if(parent::beforeDelete())
        {
            if($this->profile_image != null)
            {
                //Delete image if exist
                $config = [
                            'model'             => $this, 
                            'attribute'         => 'profile_image', 
                            'uploadInstance'    => null, 
                            'savedFile'         => null,
                            'createThumbnail'   => true
                          ];
                $fileManagerInstance = UsniAdaptor::app()->assetManager->getResourceManager('image', $config);
                $fileManagerInstance->delete();
            }
            return true;
        }
        return false;
    }
    
    /**
     * Get full name for the user.
     * @return string
     */
    public function getFullName()
    {
        if($this->firstname != null && $this->lastname != null)
        {
            return $this->firstname . ' ' . $this->lastname;
        }
        else
        {
            return UsniAdaptor::t('application', '(not set)');
        }
    }
}