<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\AccountList
 *
 * @property int $id
 * @property string $account_name
 * @property float $initial_balance
 * @property string $account_number
 * @property string $branch_code
 * @property string $bank_branch
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereBankBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereBranchCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereInitialBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountList whereUpdatedAt($value)
 */
	class AccountList extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Allowance
 *
 * @property int $id
 * @property int $employee_id
 * @property int $allowance_option
 * @property string $title
 * @property int $is_recurring
 * @property string|null $period
 * @property float $amount
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance query()
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereAllowanceOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allowance whereUpdatedAt($value)
 */
	class Allowance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AllowanceOption
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceOption whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceOption whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceOption whereUpdatedAt($value)
 */
	class AllowanceOption extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Announcement
 *
 * @property int $id
 * @property string $title
 * @property string $start_date
 * @property string $end_date
 * @property int $branch_id
 * @property string $department_id
 * @property string $employee_id
 * @property string $description
 * @property string|null $document
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement query()
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereUpdatedAt($value)
 */
	class Announcement extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AnnouncementEmployee
 *
 * @property int $id
 * @property int $announcement_id
 * @property int $employee_id
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee query()
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee whereAnnouncementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnnouncementEmployee whereUpdatedAt($value)
 */
	class AnnouncementEmployee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Appraisal
 *
 * @property int $id
 * @property int $employee_id
 * @property int|null $indicator_id
 * @property int $created_by
 * @property string|null $start_month
 * @property string|null $end_month
 * @property int $total_goal_score
 * @property float $total_goal_overall
 * @property int $total_competency_score
 * @property float $total_competency_overall
 * @property float $total_apprisal
 * @property string|null $category
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AppraisalRating> $competency_ratings
 * @property-read int|null $competency_ratings_count
 * @property-read \App\Models\User|null $created_by_user
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GoalEvaluation> $goal_evaluations
 * @property-read int|null $goal_evaluations_count
 * @property-read \App\Models\Indicator|null $indicator
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereEndMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereIndicatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereStartMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereTotalApprisal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereTotalCompetencyOverall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereTotalCompetencyScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereTotalGoalOverall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereTotalGoalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Appraisal whereUpdatedAt($value)
 */
	class Appraisal extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AppraisalRating
 *
 * @property int $id
 * @property int $appraisal_id
 * @property int $competency_id
 * @property string|null $evaluation
 * @property int|null $rating
 * @property int|null $score
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Appraisal|null $appraisal
 * @property-read \App\Models\Competencies|null $competency
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating whereAppraisalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating whereCompetencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating whereEvaluation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppraisalRating whereUpdatedAt($value)
 */
	class AppraisalRating extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Asset
 *
 * @property int $id
 * @property string $employee_id
 * @property string $name
 * @property string $purchase_date
 * @property string $supported_date
 * @property float $amount
 * @property string|null $description
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|Asset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset query()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereSupportedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Asset whereUpdatedAt($value)
 */
	class Asset extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AttendanceEmployee
 *
 * @property int $id
 * @property int $employee_id
 * @property string $date
 * @property string|null $shift_type_id
 * @property int|null $attendance_status_id
 * @property string $status
 * @property string $clock_in
 * @property string $clock_out
 * @property string $late
 * @property string $early_leaving
 * @property string|null $work_hours
 * @property string $overtime
 * @property string $total_rest
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $attendance_type_id
 * @property string|null $coord_in
 * @property string|null $coord_out
 * @property int|null $is_valid
 * @property int|null $validate_by
 * @property string|null $note
 * @property string|null $picture_in
 * @property string|null $picture_out
 * @property string|null $source_in
 * @property string|null $source_out
 * @property-read \App\Models\AttendanceStatus|null $attendanceStatus
 * @property-read \App\Models\AttendanceType|null $attendance_type
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Employee|null $employees
 * @property-read \App\Models\ShiftType|null $shift_type
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereAttendanceStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereAttendanceTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereClockIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereClockOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereCoordIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereCoordOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereEarlyLeaving($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereIsValid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereLate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereOvertime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee wherePictureIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee wherePictureOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereShiftTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereSourceIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereSourceOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereTotalRest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereValidateBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceEmployee whereWorkHours($value)
 */
	class AttendanceEmployee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AttendanceRequest
 *
 * @property int $id
 * @property int $employee_id
 * @property int|null $shift_id
 * @property string $date
 * @property string $start_time
 * @property string $end_time
 * @property string $reason
 * @property string|null $docs
 * @property int|null $is_approved
 * @property int|null $approved_by
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\ShiftType|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereDocs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceRequest whereUpdatedAt($value)
 */
	class AttendanceRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AttendanceStatus
 *
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string|null $color
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceStatus whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceStatus whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceStatus whereName($value)
 */
	class AttendanceStatus extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AttendanceType
 *
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceType query()
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AttendanceType whereName($value)
 */
	class AttendanceType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Award
 *
 * @property int $id
 * @property int $employee_id
 * @property string $award_type
 * @property string $date
 * @property string $gift
 * @property string $description
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Award newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Award newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Award query()
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereAwardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereGift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Award whereUpdatedAt($value)
 */
	class Award extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AwardType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AwardType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AwardType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AwardType query()
 * @method static \Illuminate\Database\Eloquent\Builder|AwardType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AwardType whereUpdatedAt($value)
 */
	class AwardType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Bank
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Bank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bank whereUpdatedAt($value)
 */
	class Bank extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Branch
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_branch
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $tolerance
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Branch> $childBranch
 * @property-read int|null $child_branch_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Branch> $childBranchRecursive
 * @property-read int|null $child_branch_recursive_count
 * @property-read Branch|null $parentBranch
 * @property-read Branch|null $recursiveParentBranch
 * @method static \Illuminate\Database\Eloquent\Builder|Branch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch query()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereParentBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereTolerance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereUpdatedAt($value)
 */
	class Branch extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChFavorite
 *
 * @property int $id
 * @property int $user_id
 * @property int $favorite_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChFavorite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChFavorite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChFavorite query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChFavorite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChFavorite whereFavoriteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChFavorite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChFavorite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChFavorite whereUserId($value)
 */
	class ChFavorite extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChMessage
 *
 * @property int $id
 * @property string $type
 * @property int $from_id
 * @property int $to_id
 * @property string|null $body
 * @property string|null $attachment
 * @property int $seen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereFromId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereToId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChMessage whereUpdatedAt($value)
 */
	class ChMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Commission
 *
 * @property int $id
 * @property int $employee_id
 * @property string $title
 * @property int $is_recurring
 * @property string|null $period
 * @property float $amount
 * @property string|null $type
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Commission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereUpdatedAt($value)
 */
	class Commission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CompanyPolicy
 *
 * @property int $id
 * @property int $branch
 * @property string $title
 * @property string $description
 * @property string|null $attachment
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branches
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy query()
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy whereAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CompanyPolicy whereUpdatedAt($value)
 */
	class CompanyPolicy extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Competencies
 *
 * @property int $id
 * @property string $name
 * @property int|null $performance_type_id
 * @property string $type
 * @property string|null $description
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AppraisalRating> $Appraisals
 * @property-read int|null $appraisals_count
 * @property-read \App\Models\Performance_Type|null $performance_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IndicatorWeight> $weights
 * @property-read int|null $weights_count
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies query()
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies wherePerformanceTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competencies whereUpdatedAt($value)
 */
	class Competencies extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Complaint
 *
 * @property int $id
 * @property int $complaint_from
 * @property int $complaint_against
 * @property string $title
 * @property string $complaint_date
 * @property string $description
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereComplaintAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereComplaintDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereComplaintFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereUpdatedAt($value)
 */
	class Complaint extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Contract
 *
 * @property int $id
 * @property string|null $subject
 * @property int $employee_name
 * @property string|null $value
 * @property int $type
 * @property string $start_date
 * @property string $end_date
 * @property string|null $notes
 * @property string $status
 * @property string|null $description
 * @property string|null $contract_description
 * @property string|null $employee_signature
 * @property string|null $company_signature
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContractAttechment|null $ContractAttechment
 * @property-read \App\Models\ContractComment|null $ContractComment
 * @property-read \App\Models\ContractNote|null $ContractNote
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContractComment> $comment
 * @property-read int|null $comment_count
 * @property-read \App\Models\ContractType|null $contract_type
 * @property-read \App\Models\User|null $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContractAttechment> $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContractNote> $note
 * @property-read int|null $note_count
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCompanySignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereContractDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEmployeeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEmployeeSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereValue($value)
 */
	class Contract extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractAttechment
 *
 * @property int $id
 * @property int $contract_id
 * @property string $user_id
 * @property string $files
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment whereFiles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractAttechment whereUserId($value)
 */
	class ContractAttechment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractComment
 *
 * @property int $id
 * @property int $contract_id
 * @property string $user_id
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractComment whereUserId($value)
 */
	class ContractComment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractNote
 *
 * @property int $id
 * @property int $contract_id
 * @property int $user_id
 * @property string $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote whereContractId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractNote whereUserId($value)
 */
	class ContractNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContractType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContractType whereUpdatedAt($value)
 */
	class ContractType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CustomQuestion
 *
 * @property int $id
 * @property string $question
 * @property string|null $is_required
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomQuestion whereUpdatedAt($value)
 */
	class CustomQuestion extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DeductionOption
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DeductionOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeductionOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeductionOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeductionOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeductionOption whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeductionOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeductionOption whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeductionOption whereUpdatedAt($value)
 */
	class DeductionOption extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Department
 *
 * @property int $id
 * @property int $branch_id
 * @property string $name
 * @property int|null $overtime_limit
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @method static \Illuminate\Database\Eloquent\Builder|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereOvertimeLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Deposit
 *
 * @property int $id
 * @property int $account_id
 * @property int $amount
 * @property string $date
 * @property int $income_category_id
 * @property int $payer_id
 * @property int $payment_type_id
 * @property string|null $referal_id
 * @property string|null $description
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AccountList|null $accounts
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereIncomeCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit wherePaymentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereReferalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposit whereUpdatedAt($value)
 */
	class Deposit extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Designation
 *
 * @property int $id
 * @property int $department_id
 * @property string $name
 * @property int|null $level_id
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\LevelDesignation|null $level
 * @method static \Illuminate\Database\Eloquent\Builder|Designation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereUpdatedAt($value)
 */
	class Designation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Document
 *
 * @property int $id
 * @property string $name
 * @property string $is_required
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereUpdatedAt($value)
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DucumentUpload
 *
 * @property int $id
 * @property string $name
 * @property string $role
 * @property string $document
 * @property string|null $description
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload query()
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DucumentUpload whereUpdatedAt($value)
 */
	class DucumentUpload extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmailTemplate
 *
 * @property int $id
 * @property string $name
 * @property string|null $from
 * @property string|null $slug
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplate whereUpdatedAt($value)
 */
	class EmailTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmailTemplateLang
 *
 * @property int $id
 * @property int $parent_id
 * @property string $lang
 * @property string $subject
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailTemplateLang whereUpdatedAt($value)
 */
	class EmailTemplateLang extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Employee
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $personel_id
 * @property int|null $shift_type_id
 * @property int|null $type_id
 * @property int|null $managed_by
 * @property string $name
 * @property string|null $dob
 * @property string $gender
 * @property Employee|null $phone
 * @property string $address
 * @property string|null $domicile_address
 * @property string|null $marital_status
 * @property int $dependents
 * @property string|null $emergency_contact_number
 * @property string|null $emergency_contact_relation
 * @property string|null $emergency_contact_photo
 * @property string|null $coordinate
 * @property string $email
 * @property string $password
 * @property string $employee_id
 * @property int $branch_id
 * @property int $department_id
 * @property int $designation_id
 * @property string|null $company_doj
 * @property string|null $nationality
 * @property string|null $identity_type
 * @property string|null $identity_number
 * @property string|null $documents
 * @property int|null $bank_id
 * @property string|null $account_holder_name
 * @property string|null $account_number
 * @property string|null $tax_payer_id
 * @property int|null $salary_type
 * @property float|null $salary
 * @property int $is_active
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $terminated_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $termination_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventEmployee> $assignment
 * @property-read int|null $assignment_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AttendanceRequest> $attendanceRequests
 * @property-read int|null $attendance_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AttendanceEmployee> $attendances
 * @property-read int|null $attendances_count
 * @property-read \App\Models\Bank|null $bank
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Department|null $departments
 * @property-read \App\Models\Designation|null $designation
 * @property-read Employee|null $direct_spv
 * @property-read \App\Models\EmployeeType|null $employeeType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeHomeHistory> $home_histories
 * @property-read int|null $home_histories_count
 * @property-read Employee|null $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Overtime> $overtime
 * @property-read int|null $overtime_count
 * @property-read \App\Models\PaySlip|null $paySlip
 * @property-read Employee|null $recursiveManager
 * @property-read \App\Models\PayslipType|null $salaryType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShiftHistory> $shift_histories
 * @property-read int|null $shift_histories_count
 * @property-read \App\Models\ShiftType|null $shift_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $subordinate
 * @property-read int|null $subordinate_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $subordinateRecursive
 * @property-read int|null $subordinate_recursive_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAccountHolderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCompanyDoj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCoordinate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDependents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDomicileAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmergencyContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmergencyContactPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmergencyContactRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIdentityNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIdentityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePersonelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereSalaryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereShiftTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereTaxPayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereTerminatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereTerminationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee withoutTrashed()
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeDocument
 *
 * @property int $id
 * @property int $employee_id
 * @property int $document_id
 * @property string $document_value
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereDocumentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereUpdatedAt($value)
 */
	class EmployeeDocument extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeHomeHistory
 *
 * @property int $id
 * @property int $employee_id
 * @property string $address
 * @property string $coordinate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory whereCoordinate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeHomeHistory whereUpdatedAt($value)
 */
	class EmployeeHomeHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeType
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeType query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeType whereUpdatedAt($value)
 */
	class EmployeeType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Event
 *
 * @property int $id
 * @property int $branch_id
 * @property string $department_id
 * @property string $employee_id
 * @property string $title
 * @property string $start_date
 * @property string $end_date
 * @property string $color
 * @property string|null $description
 * @property string|null $document
 * @property string|null $location
 * @property string|null $location_coord
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventEmployee> $eventEmployees
 * @property-read int|null $event_employees_count
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereLocationCoord($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 */
	class Event extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EventEmployee
 *
 * @property int $id
 * @property int $event_id
 * @property int $employee_id
 * @property int $created_by
 * @property string|null $clock_in
 * @property string|null $clock_out
 * @property string|null $coord_in
 * @property string|null $coord_out
 * @property string|null $picture_in
 * @property string|null $picture_out
 * @property string|null $report_note
 * @property string|null $report_document
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Event|null $meeting
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereClockIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereClockOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereCoordIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereCoordOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee wherePictureIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee wherePictureOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereReportDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereReportNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventEmployee whereUpdatedAt($value)
 */
	class EventEmployee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Expense
 *
 * @property int $id
 * @property int $account_id
 * @property int $amount
 * @property string $date
 * @property int $expense_category_id
 * @property int $payee_id
 * @property int $payment_type_id
 * @property string|null $referal_id
 * @property string|null $description
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AccountList|null $accounts
 * @method static \Illuminate\Database\Eloquent\Builder|Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereExpenseCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense wherePayeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense wherePaymentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereReferalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Expense whereUpdatedAt($value)
 */
	class Expense extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ExpenseType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExpenseType whereUpdatedAt($value)
 */
	class ExpenseType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ExperienceCertificate
 *
 * @property int $id
 * @property string $lang
 * @property string $content
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperienceCertificate whereUpdatedAt($value)
 */
	class ExperienceCertificate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GenerateOfferLetter
 *
 * @property int $id
 * @property string $lang
 * @property string $content
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter query()
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GenerateOfferLetter whereUpdatedAt($value)
 */
	class GenerateOfferLetter extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Goal
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int|null $branch_id
 * @property int|null $department_id
 * @property int|null $employee_id
 * @property string $name
 * @property string|null $target
 * @property string $start_date
 * @property string $end_date
 * @property string|null $description
 * @property string|null $goal
 * @property int $progress
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Goal> $child
 * @property-read int|null $child_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Goal> $childRecursive
 * @property-read int|null $child_recursive_count
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Employee|null $employee
 * @property-read Goal|null $parent
 * @property-read Goal|null $recursiveParent
 * @method static \Illuminate\Database\Eloquent\Builder|Goal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereGoal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goal whereUpdatedAt($value)
 */
	class Goal extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GoalEvaluation
 *
 * @property int $id
 * @property int $apprisal_id
 * @property int $goal_id
 * @property string|null $evaluation
 * @property int|null $weight
 * @property int|null $rating
 * @property int|null $score
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Appraisal|null $apprisal
 * @property-read \App\Models\Goal|null $goal
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereApprisalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereEvaluation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereGoalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalEvaluation whereWeight($value)
 */
	class GoalEvaluation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GoalTracking
 *
 * @property int $id
 * @property int $branch
 * @property int $goal_type
 * @property string $start_date
 * @property string $end_date
 * @property string|null $subject
 * @property string|null $rating
 * @property string|null $target_achievement
 * @property string|null $description
 * @property int $status
 * @property int $progress
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branches
 * @property-read \App\Models\GoalType|null $goalType
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereGoalType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereTargetAchievement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalTracking whereUpdatedAt($value)
 */
	class GoalTracking extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GoalType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GoalType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalType query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoalType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoalType whereUpdatedAt($value)
 */
	class GoalType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\HealthyTarget
 *
 * @property int $id
 * @property string $activity_name
 * @property int $target
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|HealthyTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HealthyTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HealthyTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder|HealthyTarget whereActivityName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HealthyTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HealthyTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HealthyTarget whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HealthyTarget whereUpdatedAt($value)
 */
	class HealthyTarget extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Holiday
 *
 * @property int $id
 * @property string|null $end_date
 * @property string|null $start_date
 * @property string $occasion
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday query()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereOccasion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedAt($value)
 */
	class Holiday extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\IncomeType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeType query()
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeType whereUpdatedAt($value)
 */
	class IncomeType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Indicator
 *
 * @property int $id
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $level_id
 * @property-read \App\Models\LevelDesignation|null $level
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IndicatorWeight> $weights
 * @property-read int|null $weights_count
 * @method static \Illuminate\Database\Eloquent\Builder|Indicator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Indicator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Indicator query()
 * @method static \Illuminate\Database\Eloquent\Builder|Indicator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Indicator whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Indicator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Indicator whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Indicator whereUpdatedAt($value)
 */
	class Indicator extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\IndicatorWeight
 *
 * @property int $id
 * @property int|null $indicator_id
 * @property int|null $competency_id
 * @property int|null $weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Competencies|null $competency
 * @property-read \App\Models\Indicator|null $indicator
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight query()
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight whereCompetencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight whereIndicatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndicatorWeight whereWeight($value)
 */
	class IndicatorWeight extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InterviewSchedule
 *
 * @property int $id
 * @property int $candidate
 * @property int $employee
 * @property string $date
 * @property string $time
 * @property string|null $comment
 * @property string|null $employee_response
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\JobApplication|null $applications
 * @property-read \App\Models\User|null $users
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereCandidate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereEmployeeResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InterviewSchedule whereUpdatedAt($value)
 */
	class InterviewSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\IpRestrict
 *
 * @property int $id
 * @property string $ip
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IpRestrict newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpRestrict newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IpRestrict query()
 * @method static \Illuminate\Database\Eloquent\Builder|IpRestrict whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpRestrict whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpRestrict whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpRestrict whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IpRestrict whereUpdatedAt($value)
 */
	class IpRestrict extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Job
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $requirement
 * @property int $branch
 * @property int $category
 * @property string|null $skill
 * @property int|null $position
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $status
 * @property string|null $applicant
 * @property string|null $visibility
 * @property string|null $code
 * @property string|null $custom_question
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branches
 * @property-read \App\Models\JobCategory|null $categories
 * @property-read \App\Models\User|null $createdBy
 * @method static \Illuminate\Database\Eloquent\Builder|Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereApplicant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCustomQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereRequirement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereSkill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereVisibility($value)
 */
	class Job extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JobApplication
 *
 * @property int $id
 * @property int $job
 * @property string|null $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $profile
 * @property string|null $resume
 * @property string|null $cover_letter
 * @property string|null $dob
 * @property string|null $gender
 * @property string|null $address
 * @property string|null $country
 * @property string|null $state
 * @property string|null $city
 * @property string|null $zip_code
 * @property int $stage
 * @property int $order
 * @property string|null $skill
 * @property int $rating
 * @property int $is_archive
 * @property string|null $custom_question
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Job|null $jobs
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereCoverLetter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereCustomQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereIsArchive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereResume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereSkill($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplication whereZipCode($value)
 */
	class JobApplication extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JobApplicationNote
 *
 * @property int $id
 * @property int $application_id
 * @property int $note_created
 * @property string|null $note
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $noteCreated
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote whereNoteCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobApplicationNote whereUpdatedAt($value)
 */
	class JobApplicationNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JobCategory
 *
 * @property int $id
 * @property string $title
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobCategory whereUpdatedAt($value)
 */
	class JobCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JobOnBoard
 *
 * @property int $id
 * @property int $application
 * @property string|null $joining_date
 * @property string|null $status
 * @property string|null $job_type
 * @property int|null $days_of_week
 * @property int|null $salary
 * @property string|null $salary_type
 * @property string|null $salary_duration
 * @property int $convert_to_employee
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\JobApplication|null $applications
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereApplication($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereConvertToEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereDaysOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereJobType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereJoiningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereSalaryDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereSalaryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobOnBoard whereUpdatedAt($value)
 */
	class JobOnBoard extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JobStage
 *
 * @property int $id
 * @property string $title
 * @property int $order
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobStage whereUpdatedAt($value)
 */
	class JobStage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JoiningLetter
 *
 * @property int $id
 * @property string $lang
 * @property string $content
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter query()
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JoiningLetter whereUpdatedAt($value)
 */
	class JoiningLetter extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LandingPageSection
 *
 * @property int $id
 * @property string $section_name
 * @property int $section_order
 * @property string|null $content
 * @property string $section_type
 * @property string $default_content
 * @property string $section_demo_image
 * @property string $section_blade_file_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection query()
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereDefaultContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereSectionBladeFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereSectionDemoImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereSectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereSectionOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereSectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LandingPageSection whereUpdatedAt($value)
 */
	class LandingPageSection extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Leave
 *
 * @property int $id
 * @property int $employee_id
 * @property int $leave_type_id
 * @property string $applied_on
 * @property string $start_date
 * @property string $end_date
 * @property string $total_leave_days
 * @property string $leave_reason
 * @property string|null $remark
 * @property string|null $location
 * @property string|null $document_path
 * @property string|null $note
 * @property string $status
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employees
 * @property-read \App\Models\LeaveType|null $leaveType
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave query()
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereAppliedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereDocumentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLeaveReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLeaveTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereTotalLeaveDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Leave whereUpdatedAt($value)
 */
	class Leave extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeaveType
 *
 * @property int $id
 * @property string $title
 * @property int $is_active
 * @property int $days
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveType whereUpdatedAt($value)
 */
	class LeaveType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LevelDesignation
 *
 * @property int $id
 * @property string $name
 * @property int $can_self_assessment
 * @property string|null $designation_ids
 * @property int|null $goal_weight
 * @property int|null $competency_weight
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation query()
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation whereCanSelfAssessment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation whereCompetencyWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation whereDesignationIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation whereGoalWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LevelDesignation whereUpdatedAt($value)
 */
	class LevelDesignation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Loan
 *
 * @property int $id
 * @property int $employee_id
 * @property int $loan_option
 * @property string $title
 * @property int $is_recurring
 * @property string|null $period
 * @property float $amount
 * @property string|null $type
 * @property string $reason
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Loan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Loan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Loan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereLoanOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Loan whereUpdatedAt($value)
 */
	class Loan extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LoanOption
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LoanOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanOption query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoanOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanOption whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanOption whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoanOption whereUpdatedAt($value)
 */
	class LoanOption extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LogAttendance
 *
 * @property int $id
 * @property string|null $personel_id
 * @property string|null $date
 * @property string|null $coordinate
 * @property string|null $coordinate_out
 * @property string|null $min
 * @property string|null $max
 * @property int|null $shift_id
 * @property string|null $min_source
 * @property string|null $max_source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\ShiftType|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereCoordinate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereCoordinateOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereMaxSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereMinSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance wherePersonelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogAttendance whereUpdatedAt($value)
 */
	class LogAttendance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LogSyncAttendance
 *
 * @property int $id
 * @property string $status
 * @property string $date
 * @property string $unit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogSyncAttendance whereUpdatedAt($value)
 */
	class LogSyncAttendance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Meeting
 *
 * @property int $id
 * @property int $branch_id
 * @property string $department_id
 * @property string $employee_id
 * @property string $title
 * @property string $meeting_type
 * @property string|null $url
 * @property string|null $password
 * @property string $start_time
 * @property string $end_time
 * @property string|null $location
 * @property string|null $note
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereMeetingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Meeting whereUrl($value)
 */
	class Meeting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MeetingEmployee
 *
 * @property int $id
 * @property int $meeting_id
 * @property int $employee_id
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee query()
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MeetingEmployee whereUpdatedAt($value)
 */
	class MeetingEmployee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\NOC
 *
 * @property int $id
 * @property string $lang
 * @property string $content
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|NOC newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NOC newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NOC query()
 * @method static \Illuminate\Database\Eloquent\Builder|NOC whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NOC whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NOC whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NOC whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NOC whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NOC whereUpdatedAt($value)
 */
	class NOC extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OtherPayment
 *
 * @property int $id
 * @property int $employee_id
 * @property string $title
 * @property int $is_recurring
 * @property string|null $period
 * @property float $amount
 * @property string|null $type
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OtherPayment whereUpdatedAt($value)
 */
	class OtherPayment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Overtime
 *
 * @property int $id
 * @property int $employee_id
 * @property string $title
 * @property string $date
 * @property string|null $type
 * @property int|null $is_work_day
 * @property string|null $clock_in
 * @property string|null $clock_out
 * @property string|null $coord_in
 * @property string|null $coord_out
 * @property string|null $picture_in
 * @property string|null $picture_out
 * @property string|null $description
 * @property string|null $document
 * @property string|null $report_document
 * @property string|null $report_note
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime query()
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereClockIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereClockOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereCoordIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereCoordOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereIsWorkDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime wherePictureIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime wherePictureOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereReportDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereReportNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Overtime whereUpdatedAt($value)
 */
	class Overtime extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PaySlip
 *
 * @property int $id
 * @property int $employee_id
 * @property float $net_payble
 * @property string|null $bruto
 * @property string $salary_month
 * @property int $status
 * @property float $basic_salary
 * @property string $allowance
 * @property string $commission
 * @property string $loan
 * @property string $saturation_deduction
 * @property string $other_payment
 * @property string $overtime
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employees
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereAllowance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereBasicSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereBruto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereLoan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereNetPayble($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereOtherPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereOvertime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereSalaryMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereSaturationDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaySlip whereUpdatedAt($value)
 */
	class PaySlip extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Payees
 *
 * @property int $id
 * @property string $payee_name
 * @property string $contact_number
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Payees newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payees newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payees query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payees whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payees whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payees whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payees whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payees wherePayeeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payees whereUpdatedAt($value)
 */
	class Payees extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Payer
 *
 * @property int $id
 * @property string $payer_name
 * @property string $contact_number
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Payer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payer whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payer wherePayerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payer whereUpdatedAt($value)
 */
	class Payer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PaymentType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentType whereUpdatedAt($value)
 */
	class PaymentType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PayslipType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PayslipType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayslipType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayslipType query()
 * @method static \Illuminate\Database\Eloquent\Builder|PayslipType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayslipType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayslipType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayslipType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayslipType whereUpdatedAt($value)
 */
	class PayslipType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Performance_Type
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_id
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Performance_Type> $child
 * @property-read int|null $child_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Performance_Type> $childRecursive
 * @property-read int|null $child_recursive_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competencies> $competencies
 * @property-read int|null $competencies_count
 * @property-read Performance_Type|null $parent
 * @property-read Performance_Type|null $recursiveParent
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type query()
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Performance_Type whereUpdatedAt($value)
 */
	class Performance_Type extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Permit
 *
 * @property int $id
 * @property int $employee_id
 * @property int $permit_type_id
 * @property string $start_date
 * @property string $end_date
 * @property string $total_permit_days
 * @property string $reason
 * @property string|null $docs
 * @property string $status
 * @property int|null $is_approved
 * @property int|null $approved_by
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\PermitType|null $permitType
 * @method static \Illuminate\Database\Eloquent\Builder|Permit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereDocs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit wherePermitTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereTotalPermitDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permit whereUpdatedAt($value)
 */
	class Permit extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PermitType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PermitType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermitType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermitType query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermitType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermitType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermitType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermitType whereUpdatedAt($value)
 */
	class PermitType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Pph21
 *
 * @property int $id
 * @property int $employee_id
 * @property string $date
 * @property string $ptkp
 * @property string $tax_object_code
 * @property int $is_gross_up
 * @property string $bruto
 * @property string $rate
 * @property string $pph21
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereBruto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereIsGrossUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 wherePph21($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 wherePtkp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereTaxObjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pph21 whereUpdatedAt($value)
 */
	class Pph21 extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Promotion
 *
 * @property int $id
 * @property int $employee_id
 * @property int $designation_id
 * @property string $promotion_title
 * @property string $promotion_date
 * @property string $description
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Designation|null $designation
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion wherePromotionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion wherePromotionTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Promotion whereUpdatedAt($value)
 */
	class Promotion extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PushSubscription
 *
 * @property int $id
 * @property int $user_id
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PushSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|PushSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushSubscription whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushSubscription whereUserId($value)
 */
	class PushSubscription extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Report
 *
 * @property int $id
 * @property int $employee_id
 * @property int $created_by
 * @property string $type
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int $is_read
 * @property int|null $response_by
 * @property string|null $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReportAccomplishment> $accomplishments
 * @property-read int|null $accomplishments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReportActivity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReportAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReportObstacle> $obstacles
 * @property-read int|null $obstacles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReportPlan> $plans
 * @property-read int|null $plans_count
 * @property-read \App\Models\User|null $responder
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Report onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereResponseBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Report withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Report withoutTrashed()
 */
	class Report extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ReportAccomplishment
 *
 * @property int $id
 * @property int $report_id
 * @property string $accomplishment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Report|null $report
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAccomplishment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAccomplishment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAccomplishment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAccomplishment whereAccomplishment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAccomplishment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAccomplishment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAccomplishment whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAccomplishment whereUpdatedAt($value)
 */
	class ReportAccomplishment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ReportActivity
 *
 * @property int $id
 * @property int $report_id
 * @property string $date
 * @property string $activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Report|null $report
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportActivity whereUpdatedAt($value)
 */
	class ReportActivity extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ReportAttachment
 *
 * @property int $id
 * @property int $report_id
 * @property string $attachment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Report|null $report
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAttachment whereAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAttachment whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportAttachment whereUpdatedAt($value)
 */
	class ReportAttachment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ReportObstacle
 *
 * @property int $id
 * @property int $report_id
 * @property string $obstacle
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Report|null $report
 * @method static \Illuminate\Database\Eloquent\Builder|ReportObstacle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportObstacle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportObstacle query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportObstacle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportObstacle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportObstacle whereObstacle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportObstacle whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportObstacle whereUpdatedAt($value)
 */
	class ReportObstacle extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ReportPlan
 *
 * @property int $id
 * @property int $report_id
 * @property string $plan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Report|null $report
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPlan wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPlan whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPlan whereUpdatedAt($value)
 */
	class ReportPlan extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Resignation
 *
 * @property int $id
 * @property int $employee_id
 * @property string $notice_date
 * @property string $resignation_date
 * @property string $description
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation whereNoticeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation whereResignationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Resignation whereUpdatedAt($value)
 */
	class Resignation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SaturationDeduction
 *
 * @property int $id
 * @property int $employee_id
 * @property int $deduction_option
 * @property string $title
 * @property string $amount
 * @property string|null $type
 * @property int $is_recurring
 * @property string|null $period
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DeductionOption|null $deduction
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction query()
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereDeductionOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SaturationDeduction whereUpdatedAt($value)
 */
	class SaturationDeduction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SetSalary
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SetSalary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SetSalary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SetSalary query()
 * @method static \Illuminate\Database\Eloquent\Builder|SetSalary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SetSalary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SetSalary whereUpdatedAt($value)
 */
	class SetSalary extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ShiftHistory
 *
 * @property int $id
 * @property int $shift_type_id
 * @property int $employee_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\ShiftType|null $shiftType
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftHistory whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftHistory whereShiftTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftHistory whereUpdatedAt($value)
 */
	class ShiftHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ShiftTime
 *
 * @property int $id
 * @property int $shift_type_id
 * @property string $days
 * @property int $is_working
 * @property string|null $start_time
 * @property string|null $end_time
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ShiftType|null $shiftType
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereIsWorking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereShiftTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftTime withoutTrashed()
 */
	class ShiftTime extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ShiftType
 *
 * @property int $id
 * @property string $name
 * @property int|null $branch_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShiftTime> $shiftTimes
 * @property-read int|null $shift_times_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShiftHistory> $shift_histories
 * @property-read int|null $shift_histories_count
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShiftType withoutTrashed()
 */
	class ShiftType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Termination
 *
 * @property int $id
 * @property int $employee_id
 * @property string $notice_date
 * @property string $termination_date
 * @property string $termination_type
 * @property string $description
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\TerminationType|null $terminationType
 * @method static \Illuminate\Database\Eloquent\Builder|Termination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Termination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Termination query()
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereNoticeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereTerminationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereTerminationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Termination whereUpdatedAt($value)
 */
	class Termination extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TerminationType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TerminationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TerminationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TerminationType query()
 * @method static \Illuminate\Database\Eloquent\Builder|TerminationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TerminationType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TerminationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TerminationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TerminationType whereUpdatedAt($value)
 */
	class TerminationType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property string $title
 * @property int $employee_id
 * @property string $priority
 * @property string $end_date
 * @property string|null $description
 * @property string $ticket_code
 * @property int $ticket_created
 * @property int $created_by
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTicketCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTicketCreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 */
	class Ticket extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TicketReply
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $employee_id
 * @property string $description
 * @property int $created_by
 * @property int $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketReply whereUpdatedAt($value)
 */
	class TicketReply extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TimeSheet
 *
 * @property int $id
 * @property int $employee_id
 * @property string $date
 * @property float $hours
 * @property string|null $remark
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Employee|null $employees
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet query()
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TimeSheet whereUpdatedAt($value)
 */
	class TimeSheet extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Trainer
 *
 * @property int $id
 * @property \App\Models\Branch|null $branch
 * @property string $firstname
 * @property string $lastname
 * @property string $contact
 * @property string $email
 * @property string|null $address
 * @property string|null $expertise
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branches
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereExpertise($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Trainer whereUpdatedAt($value)
 */
	class Trainer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Training
 *
 * @property int $id
 * @property int $branch
 * @property int $trainer_option
 * @property int $training_type
 * @property int $trainer
 * @property float $training_cost
 * @property int $employee
 * @property string $start_date
 * @property string $end_date
 * @property string|null $description
 * @property int $performance
 * @property int $status
 * @property string|null $remarks
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch_ref
 * @property-read \App\Models\Branch|null $branches
 * @property-read \App\Models\Employee|null $employee_ref
 * @property-read \App\Models\Employee|null $employees
 * @property-read \App\Models\Trainer|null $trainer_ref
 * @property-read \App\Models\Trainer|null $trainers
 * @property-read \App\Models\TrainingType|null $type
 * @property-read \App\Models\TrainingType|null $types
 * @method static \Illuminate\Database\Eloquent\Builder|Training newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Training newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Training query()
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training wherePerformance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereTrainer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereTrainerOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereTrainingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereTrainingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereUpdatedAt($value)
 */
	class Training extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TrainingType
 *
 * @property int $id
 * @property string $name
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingType query()
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingType whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrainingType whereUpdatedAt($value)
 */
	class TrainingType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Transfer
 *
 * @property int $id
 * @property int $employee_id
 * @property int $branch_id
 * @property int $department_id
 * @property int|null $designation_id
 * @property int|null $managed_by
 * @property string|null $document_path
 * @property string $transfer_date
 * @property string $description
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Designation|null $designation
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Employee|null $managed
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereDocumentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereTransferDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereUpdatedAt($value)
 */
	class Transfer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TransferBalance
 *
 * @property int $id
 * @property int $from_account_id
 * @property int $to_account_id
 * @property string $date
 * @property int $amount
 * @property int $payment_type_id
 * @property string|null $referal_id
 * @property string|null $description
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereFromAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance wherePaymentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereReferalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereToAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TransferBalance whereUpdatedAt($value)
 */
	class TransferBalance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Travel
 *
 * @property int $id
 * @property int $employee_id
 * @property string $start_date
 * @property string $end_date
 * @property string $purpose_of_visit
 * @property string $place_of_visit
 * @property string $description
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|Travel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Travel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Travel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel wherePlaceOfVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel wherePurposeOfVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Travel whereUpdatedAt($value)
 */
	class Travel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $type
 * @property int|null $branch_id
 * @property string|null $avatar
 * @property string $lang
 * @property string|null $last_login
 * @property int $is_active
 * @property string $created_by
 * @property string|null $remember_token
 * @property string|null $activated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $active_status
 * @property int $dark_mode
 * @property string $messenger_color
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PushSubscription> $pushNotifications
 * @property-read int|null $push_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\VehicleLending|null $vehicleLendingApproval
 * @property-read \App\Models\VehicleLending|null $vehicleLendingRequest
 * @property-read \App\Models\VehicleOfficer|null $vehicleOfficer
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereActivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDarkMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMessengerColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * App\Models\UserCoupon
 *
 * @property-read \App\Models\User|null $userDetail
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCoupon query()
 */
	class UserCoupon extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserEmailTemplate
 *
 * @property int $id
 * @property int $template_id
 * @property int $user_id
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserEmailTemplate whereUserId($value)
 */
	class UserEmailTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Utility
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Utility newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Utility newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Utility query()
 */
	class Utility extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Vehicle
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property string $type
 * @property string $police_no
 * @property string $km
 * @property string $emoney_balance
 * @property int|null $branch_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $version
 * @property-read \App\Models\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleLending> $lendings
 * @property-read int|null $lendings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereEmoneyBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle wherePoliceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Vehicle withoutTrashed()
 */
	class Vehicle extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VehicleLending
 *
 * @property int $id
 * @property int $request_by
 * @property int $vehicle_id
 * @property string $date
 * @property string $end_date
 * @property string $purpose
 * @property string $status
 * @property int|null $approved_by
 * @property string|null $pickup_time
 * @property string|null $return_time
 * @property string|null $pickup_km
 * @property string|null $return_km
 * @property string $pickup_emoney_balance
 * @property string $return_emoney_balance
 * @property string|null $pickup_file_1
 * @property string|null $return_file_1
 * @property string|null $pickup_file_2
 * @property string|null $return_file_2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\User|null $requester
 * @property-read \App\Models\Vehicle|null $vehicle
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending query()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending wherePickupEmoneyBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending wherePickupFile1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending wherePickupFile2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending wherePickupKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending wherePickupTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereRequestBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereReturnEmoneyBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereReturnFile1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereReturnFile2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereReturnKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereReturnTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleLending whereVehicleId($value)
 */
	class VehicleLending extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VehicleOfficer
 *
 * @property int $id
 * @property int $user_id
 * @property int $is_resricted
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleOfficerAccess> $accesses
 * @property-read int|null $accesses_count
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer query()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer whereIsResricted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficer whereUserId($value)
 */
	class VehicleOfficer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\VehicleOfficerAccess
 *
 * @property int $id
 * @property int $officer_id
 * @property int $branch_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\VehicleOfficer|null $officer
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficerAccess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficerAccess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficerAccess query()
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficerAccess whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficerAccess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficerAccess whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficerAccess whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VehicleOfficerAccess whereUpdatedAt($value)
 */
	class VehicleOfficerAccess extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Warning
 *
 * @property int $id
 * @property int $warning_to
 * @property int $warning_by
 * @property string $subject
 * @property string $warning_date
 * @property string $description
 * @property string $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Warning newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Warning newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Warning query()
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereWarningBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereWarningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warning whereWarningTo($value)
 */
	class Warning extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ZoomMeeting
 *
 * @property int $id
 * @property string|null $title
 * @property string $meeting_id
 * @property string $user_id
 * @property string|null $password
 * @property string $start_date
 * @property int $duration
 * @property string|null $start_url
 * @property string|null $join_url
 * @property string|null $status
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereJoinUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereMeetingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereStartUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoomMeeting whereUserId($value)
 */
	class ZoomMeeting extends \Eloquent {}
}

