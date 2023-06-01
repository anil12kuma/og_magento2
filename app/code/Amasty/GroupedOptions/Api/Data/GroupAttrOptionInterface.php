<?php
namespace Amasty\GroupedOptions\Api\Data;

interface GroupAttrOptionInterface
{
    public const ID = 'group_option_id';
    public const GROUP_ID = 'group_id';
    public const OPTION_ID = 'option_id';
    public const SORT_ORDER = 'sort_order';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @return int
     */
    public function getOptionId();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param $id
     * @return GroupAttrOptionInterface
     */
    public function setId($id);

    /**
     * @param $id
     * @return GroupAttrOptionInterface
     */
    public function setGroupId($id);

    /**
     * @param $option
     * @return GroupAttrOptionInterface
     */
    public function setOptionId($option);

    /**
     * @param $sort
     * @return GroupAttrOptionInterface
     */
    public function setSortOrder($sort);
}
