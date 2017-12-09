Date: 2017-12-09
Title: Resetting the Jenkins admin password
intro: While keeping security running
Tags: Jenkins security linux
Status: public
Toc: yes
Position: 1

So, you've locked yourself out of Jenkins. You may have seen some guides that involve
that disable security for an account, then log into the account without a password,
then resetting the password and re-enabling security.

#A terrible, yet common idea:

![Jenkins bad password reset](/images/Jenkins-password-reset-bad.png)

When you leave your account open without a password, you're violating every
security principle you can think of.

You might think to yourself that the time window where your admin account
is unsecured is too short for an unauthorized entity to log in and do harm.
You tell yourself that you are careful and reset the password as soon as possible,
to keep this time window short.

Now imagine that someone else on your team asks you how to reset the password,
and you show them this technique. Now what if this person disables security
then goes off to lunch? Or goes home? Now the system is unsecured for an indefinite
amount of time.

Because you taught them this horrible technique,
you put your company's system in harm's way.

Don't ever do this.

#A much better idea:

![Jenkins good password reset](/images/Jenkins-password-reset-good.png)

The `config.xml` file in `/var/lib/Jenkins/users/admin/` acts sort of like
the `/etc/shadow` file Linux or UNIX-like systems or the SAM file in Windows, in the sense that it stores
the hash of the account's password.

If you need to reset the password without logging in, you can edit this file
and replace the old hash with a new one generated from `bcrypt`:

```
$ pip install bcrypt
$ python
>>> import bcrypt
>>> bcrypt.hashpw("yourpassword", bcrypt.gensalt(rounds=10, prefix=b"2a"))
'YOUR_HASH'
```

This will output your hash, with prefix `2a`, the correct prefix for Jenkins hashes.

Now, edit the `config.xml` file:

```
...
<passwordHash>#jbcrypt:REPLACE_THIS</passwordHash>
...
```

Once you insert the new hash, reset Jenkins:

(if you are on a system with `systemd`):

```
sudo systemctl restart Jenkins
```

You can now log in, and you didn't leave your system open for a second.
