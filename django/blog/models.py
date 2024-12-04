from django.db import models
from django.conf import settings
from django.utils import timezone

# Create your models here.
# testing
class Post(models.Model):
    author = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE)
    title = models.CharField(max_length=200)
    text = models.TextField()
    created_date = models.DateTimeField(default=timezone.now)

    def __str__(self):
        return self.title
    
class Schedule(models.Model):
    author = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.CASCADE)
    name = models.CharField(max_length=200)
    location = models.CharField(max_length=200)
    start_time = models.DateTimeField()
    end_time = models.DateTimeField()
    num_participants = models.IntegerField()
    slot_len = models.IntegerField() # ENUM: 15, 30, 45, 60 ? NA
    calendar = models.TextField() # ENUM: Google, Outlook, Yahoo, NA ???
    notes = models.TextField()
    created_date = models.DateTimeField(default=timezone.now)

    def __str__(self):
        return self.name
