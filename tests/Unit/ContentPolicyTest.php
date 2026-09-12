<?php

namespace Tests\Unit;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

class ContentPolicyTest extends TestCase
{
    public function test_draft_post_access_is_limited_to_the_owner_and_moderators(): void
    {
        $owner = $this->user(1, UserRole::User);
        $other = $this->user(2, UserRole::User);
        $moderator = $this->user(3, UserRole::Moderator);
        $post = (new Post)->forceFill(['user_id' => 1, 'status' => PostStatus::Draft]);
        $policy = new PostPolicy;

        $this->assertFalse($policy->view(null, $post)->allowed());
        $this->assertSame(404, $policy->view($other, $post)->status());
        $this->assertTrue($policy->view($owner, $post)->allowed());
        $this->assertTrue($policy->before($moderator, 'view'));
        $this->assertNull($policy->before($other, 'view'));

        $post->status = PostStatus::Published;
        $this->assertTrue($policy->view(null, $post)->allowed());
        $this->assertTrue($policy->update($owner, $post));
        $this->assertFalse($policy->delete($other, $post));
    }

    public function test_hidden_comment_access_and_deletion_follow_ownership(): void
    {
        $postOwner = $this->user(1, UserRole::User);
        $commentAuthor = $this->user(2, UserRole::User);
        $other = $this->user(3, UserRole::User);
        $moderator = $this->user(4, UserRole::Moderator);
        $post = (new Post)->forceFill(['user_id' => 1, 'status' => PostStatus::Published]);
        $comment = (new Comment)->forceFill(['user_id' => 2, 'status' => CommentStatus::Hidden]);
        $comment->setRelation('post', $post);
        $policy = new CommentPolicy;

        $this->assertFalse($policy->view(null, $comment));
        $this->assertFalse($policy->view($other, $comment));
        $this->assertTrue($policy->view($commentAuthor, $comment));
        $this->assertTrue($policy->view($postOwner, $comment));
        $this->assertTrue($policy->delete($commentAuthor, $comment));
        $this->assertTrue($policy->delete($postOwner, $comment));
        $this->assertFalse($policy->delete($other, $comment));
        $this->assertFalse($policy->moderate($postOwner, $comment));
        $this->assertTrue($policy->before($moderator, 'moderate'));

        $post->status = PostStatus::Draft;
        $this->assertFalse($policy->create(null, $post));
    }

    public function test_only_an_administrator_can_change_another_users_role(): void
    {
        $administrator = $this->user(1, UserRole::Administrator);
        $otherAdministrator = $this->user(2, UserRole::Administrator);
        $member = $this->user(3, UserRole::User);
        $policy = new UserPolicy;

        $this->assertTrue($policy->viewAny($administrator));
        $this->assertFalse($policy->viewAny($member));
        $this->assertTrue($policy->updateRole($administrator, $member));
        $this->assertTrue($policy->updateRole($administrator, $otherAdministrator));
        $this->assertFalse($policy->updateRole($administrator, $administrator));
        $this->assertFalse($policy->updateRole($member, $administrator));
    }

    private function user(int $id, UserRole $role): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $role]);
    }
}
