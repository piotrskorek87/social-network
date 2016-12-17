<?php

namespace Chatty\Models;

use Chatty\Models\Status;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract
                                    
{
    use Authenticatable;

    protected $table = 'users';

    protected $fillable = ['username', 'email', 'password', 'first_name', 'last_name', 'location'];

    protected $hidden = ['password', 'remember_token'];

    public function getName(){
        if($this->first_name && $this->last_name){
            return "{$this->first_name} {$this->last_name}";
        }

        if($this->first_name){
            return $this->first_name;
        }

        return null;
    }

    public function getNameOrUsername(){
        return $this->getName() ?: $this->username;
    }

    public function getFirstNameOrUsername(){
        return $this->first_name ?: $this->username;
    }

    public function getAvatarUrl(){
        return "https://www.gravatar.com/avatar/{{md5($this->email)}}?d=mm&s=40";
    }

    public function statuses(){
        return $this->hasMany('Chatty\Models\Status', 'user_id');
    }

    public function likes(){
        return $this->hasMany('Chatty\Models\Like', 'user_id'); 
    }

            //friend_id sender of an invitation(friender)
            // you match your user()->id with the first field 'xxx'
    public function friendsOfMine(){
        return $this->belongsToMany('Chatty\Models\User', 'friends', 'user_id', 'friend_id');
            //who friended you / sent you invitation / you want to get friend_ids (ids of guys that sent you an invite) / friend_id - friender, user_id - friendee(you)
    }

    public function friendOf(){
        return $this->belongsToMany('Chatty\Models\User', 'friends', 'friend_id', 'user_id');
            //who did you friended / sent them invitation / you match by friend_id and get back user_ids / friend_id - friender(you), user_id - friendee
    }

    public function friends(){
        return $this->friendsOfMine()->wherePivot('accepted', true)->get()->merge($this->friendOf()->wherePivot('accepted', true)->get());
    }

    public function friendRequests(){
        return $this->friendsOfMine()->wherePivot('accepted', false)->get();
        //some one has sent you an invitation
    }

    public function friendRequestsPending(){
        return $this->friendOf()->wherePivot('accepted', false)->get();
        //you sent an invitation
    }

    public function hasFriendRequestPending(User $user){
        return (bool)$this->friendRequestsPending()->where('id', $user->id)->count();
        //you sent an invitation to this user, his id = user_id(friendee)
        //did we sent HIm an invite 
    }
    public function hasFriendRequestReceived(User $user){
        return (bool)$this->friendRequests()->where('id', $user->id)->count();
        //if we have received invitation from this user friendsOfMine(you want to check if friend_id = id(friender))
        //the result that you get from friendsOfMine='id'
        //friend_id(result of friendsOfMine) = user->id  user object
        //did He sent you an invite 
    }
    public function addFriend(User $user){
        $this->friendOf()->attach($user->id);
        //you are sending invites
    }
    public function deleteFriend(User $user){
        $this->friendOf()->detach($user->id);
        $this->friendsOfMine()->detach($user->id);
    }
    public function acceptFriendRequest(User $user){
        $this->friendRequests()->where('id', $user->id)->first()->pivot->update([
            'accepted' => true,
        ]);
        //who sent you an invite / freinded you/ get all freinder_ids / match friender_id with $user->id / update pivot table
    }
    public function isFriendsWith(User $user){
        return (bool)$this->friends()->where('id', $user->id)->count();
        //whatever results you receive(friend_id->id or user_id->id) you match with $user->id
    }

    public function hasLikedStatus(Status $status){
        return (bool) $status->likes->where('user_id', $this->id)->count();
    }
}
