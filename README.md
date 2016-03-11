# Antidote - Database Structural Revisioning Management


## Purpose
To manage structural changes of one or many data sources (mysql, mariadb, percona or postgresql). This management is done by splitting revisions into different branches, adviced per table, in an incremental pattern.

## Syntax
              ___    __   __  ___  ___
     /\  |\ |  |  | |  \ /  \  |  |__
    /~~\ | \|  |  | |__/ \__/  |  |___

    Version 1.1.5046744f2737e580c2b57ab4fdb8d9d6c85dc5fe
    -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    +---------------------+---------------------------------------------------------------------------------------------------------------------------+----------+
    | Arguments           | Description                                                                                                               | REQUIRED |
    +---------------------+---------------------------------------------------------------------------------------------------------------------------+----------+
    | --database          | Set which database to use to connect to                                                                                   | Yes      |
    | --branch            | Which branch do you want to handle? When listing you can use --branch=! to list all branchs                               | Yes      |
    | --goto              | Which revision do you want to be the active one?                                                                          | No       |
    | --reset             | Undo all revisions of a branch                                                                                            | No       |
    | --reset_locks       | Remove all lock files                                                                                                     | No       |
    | --fastforward       | Move to a revision without applying or undoing that revision or revisions on the way                                      | No       |
    | --list              | List revisions. If you give --branch=! you see a list per branch, if you give --branch=TableName you only see that branch | No       |
    | --export            | Export a revision into /home/david/repos/antidote/vault/exports/                                                          | No       |
    | --create            | Create a new revision from a template. This will be in the order for the branch you supply                                | No       |
    | --nodecode          | Don't ut8-decode the queries before running                                                                               | No       |
    | --help              | View this information                                                                                                     | No       |
    | --about             | View information about the project                                                                                        | No       |
    | --database_type     | Database type ( mysql, pgsql )                                                                                            | Yes      |
    | --database_schema   | Database schema when pgsql                                                                                                | No       |
    | --database_host     | Database hostname                                                                                                         | Yes      |
    | --database_port     | Database port                                                                                                             | No       |
    | --database_username | Database username                                                                                                         | Yes      |
    | --database_password | Database password                                                                                                         | Yes      |
    | --noheader          | Removes the headers of antidote. Useful for integration                                                                   | No       |
    | --chain             | Chain commands to run dividing them with a comma -sign                                                                    | No       |
    | --nocolour          | Remove all coloured output from Antidote                                                                                  | No       |
    | --noclear           | Disable the terminal system clear                                                                                         | No       |
    | --notify            | Notify on chains by emailing a chain log                                                                                  | No       |
    | --bench             | Print benchmark numbers                                                                                                   | No       |
    | --preset            | Use a preset from presets.ini. Example --preset=MyPreset - This will override any command line arguments sent in.         | No       |
    +---------------------+---------------------------------------------------------------------------------------------------------------------------+----------+
    +---------------------------------------------------------------+---------------------------------------------------------------------------+
    | Command                                                       | Description                                                               |
    +---------------------------------------------------------------+---------------------------------------------------------------------------+
    | antidote --database=MyDB --branch=MyTable --list              | List the revisions for the branch MyTable inside the database MyDB        |
    | antidote --database=MyDB --branch=! --list                    | List all the revisions for all branches inside the database MyDB          |
    | antidote --database=MyDB --branch=MyTable --create            | Creates an empty revision for the branch MyTable inside the database MyDB |
    | antidote --database=MyDB --branch=! --chain=reset,goto:!,list | Chain commands to run through                                             |
    +---------------------------------------------------------------+---------------------------------------------------------------------------+


    -------------------------------------------------------------------------------------------------------------------------------------------------------------------------


## License
It has been released under the GPLv3 license which can be found here: http://www.gnu.org/licenses/gpl-3.0.html
The original author of Antidote is David V. Wallin ( david@dwall.in ).

Please note that neither the developers of this software, nor any company or organisation related to the developers of this software,
take any responsibility for the usage, or result of usage, of this software ( or related code or materials ).
