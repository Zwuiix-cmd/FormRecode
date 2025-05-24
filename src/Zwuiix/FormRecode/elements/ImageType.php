<?php

namespace Zwuiix\FormRecode\elements;

enum ImageType: int
{
    /** Image loaded from the game's resource pack */
    case GAME = 0;

    /** Image loaded from an external URL */
    case URL = 1;
}
