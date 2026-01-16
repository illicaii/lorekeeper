{!! Form::open(['url' => 'admin/character/image/' . $image->id . '/skills']) !!}
<p>Add or Remove skills manually</p>
<div class="form-group">
    {!! Form::label('Skills') !!}
    <div><a href="#" class="btn btn-primary mb-2" id="add-skill">Add Skill</a></div>
    <div id="skillList">
        @foreach ($image->skills as $skill)
            <div class="d-flex mb-2">
                {!! Form::select('skill_id[]', $skills, $skill->skill_id, ['class' => 'form-control mr-2 skill-select original', 'placeholder' => 'Select Skill']) !!}
                {!! Form::text('skill_data[]', $skill->data, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
                {!! Form::number('skill_xp[]', $skill->xp ? $skill->xp : 0, ['class' => 'form-control mr-2', 'placeholder' => 'Manual XP level (Defaults to 0)']) !!}
                <a href="#" class="remove-skill btn btn-danger mb-2">×</a>
            </div>
        @endforeach
    </div>
    <div class="skill-row hide mb-2">
        {!! Form::select('skill_id[]', $skills, null, ['class' => 'form-control mr-2 skill-select', 'placeholder' => 'Select Skill']) !!}
        {!! Form::text('skill_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
        {!! Form::number('skill_xp[]', 0, ['class' => 'form-control mr-2', 'placeholder' => 'Manual XP level (Defaults to 0)']) !!}
        <a href="#" class="remove-skill btn btn-danger mb-2">×</a>
    </div>
</div>

<div class="text-right">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('.original.skill-select').selectize({
            render: {
                item: skillSelectedRender
            }
        });
        $('#add-skill').on('click', function(e) {
            e.preventDefault();
            addSkillRow();
        });
        $('.remove-skill').on('click', function(e) {
            e.preventDefault();
            removeSkillRow($(this));
        })

        function addSkillRow() {
            var $clone = $('.skill-row').clone();
            $('#skillList').append($clone);
            $clone.removeClass('hide skill-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-skill').on('click', function(e) {
                e.preventDefault();
                removeSkillRow($(this));
            })

            $clone.find('.skill-select').selectize({
                render: {
                    item: skillSelectedRender
                }
            });
        }

        function removeSkillRow($trigger) {
            $trigger.parent().remove();
        }

        function skillSelectedRender(item, escape) {
            return '<div><span>' + escape(item["text"].trim()) + ' (' + escape(item["optgroup"].trim()) + ')' + '</span></div>';
        }
    });
</script>
