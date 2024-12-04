from django.shortcuts import redirect, render

# Create your views here.
from django.utils import timezone
from blog.models import Post
from .forms import ScheduleForm  # Import the ScheduleForm
from .models import Schedule  # Import the Schedule model

# Create your views here.
def post_list(request):
    posts = Post.objects.filter(created_date__lte=timezone.now()).order_by('created_date')

    return render(request, 'blog/posts_list.html', {
        'posts': posts
    })

def schedule(request):
    # Fetch all schedules ordered by created_date (newest first)
    schedules = Schedule.objects.filter(created_date__lte=timezone.now()).order_by('-created_date')
    return render(request, 'blog/scheduling.html', {
        'schedules': schedules  # Pass to the template
    })

def create_schedule(request):
    if request.method == 'POST':
        form = ScheduleForm(request.POST)
        if form.is_valid():
            schedule = form.save(commit=False)
            schedule.author = request.user  # Assign the logged-in user as the author
            schedule.save()
            return redirect('success')  # Redirect to a success page or reload the page
    else:
        form = ScheduleForm()
    
    schedules = Schedule.objects.all()  # Fetch existing schedules for display
    return render(request, 'blog/scheduling.html', {'form': form, 'schedules': schedules})

def success(request):
    return render(request, 'blog/success.html', {})  # Render a success page