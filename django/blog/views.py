from django.shortcuts import render

# Create your views here.
from django.utils import timezone
from blog.models import Post
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