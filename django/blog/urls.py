from django.urls import path
from . import views

urlpatterns = [
    path('', views.post_list, name='posts_list'),
    path('schedule/', views.schedule, name='schedule'),
    path('create/', views.create_schedule, name='create_schedule'),
    path('success/', views.success, name='success'),
]