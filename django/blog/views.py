from django.shortcuts import render

# Create your views here.
from django.utils import timezone
from blog.models import Post

# Create your views here.
def post_list(request):
    posts = Post.objects.filter(created_date__lte=timezone.now()).order_by('created_date')

    return render(request, 'blog/posts_list.html', {
        'posts': posts
    })