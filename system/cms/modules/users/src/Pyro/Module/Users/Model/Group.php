<?php namespace Pyro\Module\Users\Model;

use Cartalyst\Sentry\Groups\Eloquent\Group as EloquentGroup;

/**
 * Group model
 *
 * @author		PyroCMS Dev Team
 * @package		PyroCMS\Core\Modules\Users\Models
 *
 */
class Group extends EloquentGroup
{
    /**
     * Define the table name
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that aren't mass assignable
     *
     * @var array
     */
    protected $guarded = array();

    /**
     * Get all groups as a flat array
     *
     * @return array
     */
    public static function getGroupOptions()
    {
        return static::lists('description', 'id');
    }

    /**
     * Get all groups except the Admin as a flat array
     *
     * @return array
     */
    public static function getGeneralGroupOptions()
    {
        return static::where('name', '!=', 'admin')->lists('description', 'id');
    }

    /**
     * Get groups by ids as a flat array
     *
     * @param ids - The group ids to get
     * @return array
     */
    public static function findManyGroupOptionsInId($ids = array())
    {
        return static::findManyInId($ids)->lists('description', 'id');
    }

    /**
     * Get groups by ids as a collection
     *
     * @param ids - The group ids to get
     * @return collection
     */
    public static function findManyInId($ids = array())
    {
        return static::whereIn('id', $ids)->get();
    }

    /**
     * Get group by name
     *
     * @param string - The group name to get
     * @return array
     */
    public static function findByName($group_name)
    {
        return static::where('name', '=', $group_name)->first();
    }


    /**
     * Return true if they are in the group or array of groups sent
     *
     * @param bool $groups
     * @return bool
     */
    public static function userIsInGroupSlug($groups = false, $user = false)
    {

        // Do we have a valid $user object?
        if(!$user) {

            // If we don't have a current_user object either
            if(!$user = ci()->current_user) {
                return false;
            }

        }


        // they have to send a group or array of groups
        if($groups) {

            // Assume we don't have a match
            $match = false;

            // Get the logged in user object, it's all there
            $user_groups = array();
            foreach($user->groups as $group) {
                $user_groups[] = $group->name;
            }

            // If they aren't in any groups, which really shouldn't happen, we return false
            if(empty($user_groups)) return false;

            // if they sent a single value instead of an array, turn it into an array
            if(!is_array($groups)) {

                $groups = array($groups);

            }

            // Go through $groups and see if any of them are in there
            foreach($groups as $group) {

                if(in_array($group, $user_groups)) $match = true;

            }

            return $match;


        }

        // they didn't specify a group
        return false;


    }
}
